<?php

use App\Enums\OrderStatus;
use App\Models\Course;
use App\Models\CoursePurchase;
use App\Models\Order;
use App\Models\Plan;
use App\Models\User;
use App\Services\Midtrans;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    config([
        'services.midtrans.server_key' => 'server-key',
        'services.midtrans.client_key' => 'client-key',
        'services.midtrans.is_production' => false,
    ]);
});

function fakeSnapToken(): void
{
    Http::fake([
        'app.sandbox.midtrans.com/snap/v1/transactions' => Http::response(['token' => 'snap-token-123', 'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v3/redirection/snap-token-123'], 201),
    ]);
}

test('plan checkout creates a pending order priced from the database', function () {
    $this->freezeSecond();
    fakeSnapToken();
    $user = User::factory()->create();
    $plan = Plan::factory()->create(['price' => 149_000]);

    $this->actingAs($user)
        ->postJson(route('checkout.plan', $plan), ['amount' => 1_000, 'price' => 1_000])
        ->assertOk()
        ->assertJson([
            'snap_token' => 'snap-token-123',
            'client_key' => 'client-key',
            'snap_js_url' => 'https://app.sandbox.midtrans.com/snap/snap.js',
        ]);

    $order = Order::sole();
    expect($order->status)->toBe(OrderStatus::Pending)
        ->and($order->amount)->toBe(149_000)
        ->and($order->code)->toMatch('/^ORD-\d{8}-[A-Z0-9]{6}$/')
        ->and($order->purchasable->is($plan))->toBeTrue()
        ->and($order->expires_at->equalTo(now()->addHours(24)))->toBeTrue();

    Http::assertSent(fn (Request $request) => $request['transaction_details'] === ['order_id' => $order->code, 'gross_amount' => 149_000]
        && $request['enabled_payments'] === Midtrans::ENABLED_PAYMENTS
        && $request['expiry']['duration'] === 24 * 60
        && $request->hasHeader('Authorization', 'Basic '.base64_encode('server-key:')));
});

test('inactive plan cannot be checked out', function () {
    $plan = Plan::factory()->inactive()->create();

    $this->actingAs(User::factory()->create())
        ->postJson(route('checkout.plan', $plan))
        ->assertNotFound();

    Http::assertNothingSent();
});

test('checkout of an owned course is rejected', function () {
    $purchase = CoursePurchase::factory()->create();
    $purchase->course->update(['price' => 299_000]);

    $this->actingAs($purchase->user)
        ->postJson(route('checkout.course', $purchase->course))
        ->assertUnprocessable()
        ->assertJson(['message' => 'Kamu sudah memiliki course ini.']);

    expect(Order::count())->toBe(0);
    Http::assertNothingSent();
});

test('course that is not sold individually cannot be checked out', function () {
    $course = Course::factory()->published()->create(['price' => null]);

    $this->actingAs(User::factory()->create())
        ->postJson(route('checkout.course', $course))
        ->assertNotFound();
});

test('pending order for the same item is reused', function () {
    fakeSnapToken();
    $user = User::factory()->create();
    $plan = Plan::factory()->create();

    $first = $this->actingAs($user)->postJson(route('checkout.plan', $plan))->json('snap_token');
    $second = $this->actingAs($user)->postJson(route('checkout.plan', $plan))->json('snap_token');

    expect(Order::count())->toBe(1)->and($second)->toBe($first);
    Http::assertSentCount(1);
});

test('gateway error returns a clear message and leaves no order', function () {
    Http::fake(['app.sandbox.midtrans.com/*' => Http::response(['error_messages' => ['Access denied']], 401)]);

    $this->actingAs(User::factory()->create())
        ->postJson(route('checkout.plan', Plan::factory()->create()))
        ->assertStatus(502)
        ->assertJson(['message' => 'Pembayaran belum bisa disiapkan. Coba lagi beberapa saat.']);

    expect(Order::count())->toBe(0);
});

test('finish page of another user is forbidden', function () {
    $order = Order::factory()->create();

    $this->actingAs(User::factory()->create())
        ->get(route('checkout.finish', $order))
        ->assertForbidden();

    $this->actingAs($order->user)
        ->get(route('checkout.finish', $order))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('checkout/finish')
            ->where('order.status', 'pending')
            ->missing('order.snap_token'));
});

test('status check activates a paid order through the get status api', function () {
    $order = Order::factory()->create(['amount' => 149_000]);
    Http::fake([
        "api.sandbox.midtrans.com/v2/{$order->code}/status" => Http::response([
            'status_code' => '200',
            'order_id' => $order->code,
            'gross_amount' => '149000.00',
            'transaction_status' => 'settlement',
            'payment_type' => 'qris',
        ]),
    ]);

    $this->actingAs($order->user)
        ->post(route('checkout.status', $order))
        ->assertRedirect();

    expect($order->refresh()->status)->toBe(OrderStatus::Paid)
        ->and($order->user->subscriptions()->count())->toBe(1);
});

test('status check keeps the order pending when the transaction does not exist yet', function () {
    $order = Order::factory()->create();
    Http::fake([
        "api.sandbox.midtrans.com/v2/{$order->code}/status" => Http::response(['status_code' => '404', 'status_message' => "Transaction doesn't exist."], 404),
    ]);

    $this->actingAs($order->user)
        ->post(route('checkout.status', $order))
        ->assertRedirect();

    expect($order->refresh()->status)->toBe(OrderStatus::Pending);
});

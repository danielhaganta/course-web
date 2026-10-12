<?php

use App\Enums\OrderStatus;
use App\Models\Course;
use App\Models\CoursePurchase;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Subscription;

beforeEach(function () {
    config(['services.midtrans.server_key' => 'server-key']);
});

/**
 * Payload seperti yang dikirim Midtrans, dengan signature yang valid.
 *
 * @param  array<string, string>  $overrides
 * @return array<string, string>
 */
function midtransNotification(Order $order, array $overrides = []): array
{
    $payload = [
        'order_id' => $order->code,
        'status_code' => '200',
        'gross_amount' => number_format($order->amount, 2, '.', ''),
        'transaction_status' => 'settlement',
        'fraud_status' => 'accept',
        'payment_type' => 'bank_transfer',
        'transaction_id' => 'trx-123',
        ...$overrides,
    ];
    $payload['signature_key'] ??= hash('sha512', $payload['order_id'].$payload['status_code'].$payload['gross_amount'].'server-key');

    return $payload;
}

test('notification with an invalid signature is rejected and does not change the order', function () {
    $order = Order::factory()->create();

    $this->postJson(route('webhooks.midtrans'), midtransNotification($order, ['signature_key' => 'palsu']))
        ->assertForbidden();

    expect($order->refresh()->status)->toBe(OrderStatus::Pending)
        ->and(Subscription::count())->toBe(0);
});

test('notification with a different gross amount is not processed', function () {
    $order = Order::factory()->create(['amount' => 149_000]);

    $this->postJson(route('webhooks.midtrans'), midtransNotification($order, ['gross_amount' => '1000.00']))
        ->assertUnprocessable();

    expect($order->refresh()->status)->toBe(OrderStatus::Pending)
        ->and(Subscription::count())->toBe(0);
});

test('settlement for a plan marks the order paid and creates a subscription', function () {
    $this->freezeSecond();
    $plan = Plan::factory()->create(['duration_days' => 30]);
    $order = Order::factory()->create(['purchasable_id' => $plan->id, 'amount' => $plan->price]);

    $this->postJson(route('webhooks.midtrans'), midtransNotification($order))->assertOk();

    $order->refresh();
    $subscription = Subscription::sole();
    expect($order->status)->toBe(OrderStatus::Paid)
        ->and($order->paid_at)->not->toBeNull()
        ->and($order->payment_type)->toBe('bank_transfer')
        ->and($order->gateway_payload['transaction_id'])->toBe('trx-123')
        ->and($subscription->user_id)->toBe($order->user_id)
        ->and($subscription->plan_id)->toBe($plan->id)
        ->and($subscription->order_id)->toBe($order->id)
        ->and($subscription->ends_at->equalTo(now()->addDays(30)))->toBeTrue();
});

test('settlement for a course creates a course purchase', function () {
    $course = Course::factory()->published()->create(['price' => 299_000]);
    $order = Order::factory()->forCourse($course)->create();

    $this->postJson(route('webhooks.midtrans'), midtransNotification($order))->assertOk();

    expect($order->refresh()->status)->toBe(OrderStatus::Paid)
        ->and(CoursePurchase::where(['user_id' => $order->user_id, 'course_id' => $course->id])->exists())->toBeTrue();
});

test('settlement sent twice does not create a duplicate subscription', function () {
    $order = Order::factory()->create();
    $notification = midtransNotification($order);

    $this->postJson(route('webhooks.midtrans'), $notification)->assertOk();
    $this->postJson(route('webhooks.midtrans'), $notification)->assertOk();

    expect(Subscription::count())->toBe(1);
});

test('buying the same plan while it is active extends the end date', function () {
    $plan = Plan::factory()->create(['duration_days' => 30]);
    $subscription = Subscription::factory()->for($plan)->create(['ends_at' => now()->addDays(10)]);
    $order = Order::factory()->create(['user_id' => $subscription->user_id, 'purchasable_id' => $plan->id, 'amount' => $plan->price]);
    $previousEndsAt = $subscription->ends_at;

    $this->postJson(route('webhooks.midtrans'), midtransNotification($order))->assertOk();

    expect(Subscription::count())->toBe(1)
        ->and($subscription->refresh()->ends_at->equalTo($previousEndsAt->addDays(30)))->toBeTrue();
});

test('paid order does not change on an expire notification', function () {
    $order = Order::factory()->paid()->create();

    $this->postJson(route('webhooks.midtrans'), midtransNotification($order, ['transaction_status' => 'expire', 'status_code' => '407']))
        ->assertOk();

    expect($order->refresh()->status)->toBe(OrderStatus::Paid);
});

test('transaction status maps to the order status', function (string $transactionStatus, array $extra, OrderStatus $expected) {
    $order = Order::factory()->create();

    $this->postJson(route('webhooks.midtrans'), midtransNotification($order, ['transaction_status' => $transactionStatus, ...$extra]))
        ->assertOk();

    expect($order->refresh()->status)->toBe($expected)
        ->and(Subscription::count())->toBe($expected === OrderStatus::Paid ? 1 : 0);
})->with([
    'pending' => ['pending', ['status_code' => '201'], OrderStatus::Pending],
    'expire' => ['expire', ['status_code' => '407'], OrderStatus::Expired],
    'deny' => ['deny', [], OrderStatus::Failed],
    'cancel' => ['cancel', [], OrderStatus::Failed],
    'failure' => ['failure', [], OrderStatus::Failed],
    'capture accepted' => ['capture', ['fraud_status' => 'accept'], OrderStatus::Paid],
    'capture challenged' => ['capture', ['fraud_status' => 'challenge'], OrderStatus::Pending],
]);

test('notification for an unknown order still returns 200', function () {
    $order = Order::factory()->make(['code' => 'payment_notif_test_G123']);

    $this->postJson(route('webhooks.midtrans'), midtransNotification($order))->assertOk();

    expect(Order::count())->toBe(0);
});

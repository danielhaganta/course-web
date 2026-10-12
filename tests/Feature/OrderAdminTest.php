<?php

use App\Enums\OrderStatus;
use App\Filament\Resources\Subscriptions\Pages\ManageSubscriptions;
use App\Filament\Widgets\MembershipStats;
use App\Models\Order;
use App\Models\Subscription;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Number;
use Livewire\Livewire;

function rupiah(int $amount): string
{
    return (string) Number::currency($amount, in: 'IDR', locale: 'id', precision: 0);
}

test('member cannot access the order resource', function () {
    $order = Order::factory()->create();

    $this->actingAs($order->user)->get('/admin/orders')->assertForbidden();
    $this->actingAs($order->user)->get("/admin/orders/{$order->code}")->assertForbidden();
});

test('admin can see the order list and payload detail', function () {
    $this->actingAs(User::factory()->create(['is_admin' => true]));
    $order = Order::factory()->paid()->create([
        'payment_type' => 'bank_transfer',
        'gateway_payload' => ['transaction_status' => 'settlement', 'va_numbers' => [['bank' => 'bca', 'va_number' => '12345']]],
    ]);

    $this->get('/admin/orders')->assertOk()->assertSee($order->code)->assertSee('BCA Virtual Account');
    $this->get("/admin/orders/{$order->code}")->assertOk()->assertSee('&quot;transaction_status&quot;: &quot;settlement&quot;', escape: false);
});

test('revenue only counts paid orders in the right month', function () {
    // 15 Oktober 2026 10:00 WIB
    $this->travelTo(Carbon::parse('2026-10-15 10:00', 'Asia/Jakarta'));
    $this->actingAs(User::factory()->create(['is_admin' => true]));

    $paid = fn (int $amount, string $paidAtUtc) => Order::factory()->paid()->create(['amount' => $amount, 'paid_at' => Carbon::parse($paidAtUtc, 'UTC')]);
    $paid(149_000, '2026-10-05 03:00');
    $paid(249_000, '2026-10-14 08:00');
    // 1 Oktober 01:00 WIB, masih 30 September dalam UTC
    $paid(1_000_000, '2026-09-30 18:00');
    // 30 September 23:00 WIB
    $paid(1_490_000, '2026-09-30 16:00');
    Order::factory()->create(['amount' => 999_000, 'created_at' => now()]);
    Order::factory()->create(['amount' => 888_000, 'status' => OrderStatus::Failed]);
    $paid(777_000, '2026-08-20 03:00');

    Subscription::factory()->count(2)->for(User::factory())->create();
    Subscription::factory()->create();
    Subscription::factory()->expired()->create();

    Livewire::test(MembershipStats::class)
        ->assertSeeText(rupiah(1_398_000))
        ->assertSeeText(rupiah(1_490_000))
        ->assertDontSeeText(rupiah(999_000))
        ->assertSeeInOrder(['Paid orders this month', '3', 'Active subscribers', '2']);
});

test('admin can end a subscription now', function () {
    $this->freezeSecond();
    $this->actingAs(User::factory()->create(['is_admin' => true]));
    $subscription = Subscription::factory()->create();

    Livewire::test(ManageSubscriptions::class)
        ->callAction(TestAction::make('endNow')->table($subscription));

    expect($subscription->refresh()->ends_at->equalTo(now()))->toBeTrue();
});

test('delete is hidden for subscriptions from a paid order', function () {
    $this->actingAs(User::factory()->create(['is_admin' => true]));
    $fromOrder = Subscription::factory()->create(['order_id' => Order::factory()->paid()]);
    $manual = Subscription::factory()->create();

    Livewire::test(ManageSubscriptions::class)
        ->assertActionHidden(TestAction::make(DeleteAction::class)->table($fromOrder))
        ->assertActionVisible(TestAction::make(DeleteAction::class)->table($manual));
});

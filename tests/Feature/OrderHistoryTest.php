<?php

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('member only sees their own orders, newest first', function () {
    $user = User::factory()->create();
    $older = Order::factory()->for($user)->create();
    $newer = Order::factory()->for($user)->paid()->create();
    Order::factory()->create();

    $this->actingAs($user)
        ->get(route('orders.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('orders/index')
            ->has('orders', 2)
            ->where('orders.0.code', $newer->code)
            ->where('orders.0.status_label', 'Paid')
            ->where('orders.1.code', $older->code)
            ->where('orders.1.status_label', 'Awaiting payment'));
});

test('continue payment is only offered for pending orders that have not expired', function (array $attributes, bool $isPayable) {
    $order = Order::factory()->create(['snap_token' => 'snap-token-123', ...$attributes]);

    $this->actingAs($order->user)
        ->get(route('orders.index'))
        ->assertInertia(fn (Assert $page) => $isPayable
            ? $page->where('orders.0.payment', [
                'snap_token' => 'snap-token-123',
                'finish_url' => route('checkout.finish', $order),
            ])
            : $page->where('orders.0.payment', null));
})->with([
    // Closure: now() harus dievaluasi saat test berjalan, setelah timezone aplikasi aktif
    'pending, masih berlaku' => [fn () => ['status' => OrderStatus::Pending, 'expires_at' => now()->addHour()], true],
    'pending, lewat waktu' => [fn () => ['status' => OrderStatus::Pending, 'expires_at' => now()->subMinute()], false],
    'paid' => [fn () => ['status' => OrderStatus::Paid, 'paid_at' => now()], false],
    'failed' => [fn () => ['status' => OrderStatus::Failed], false],
    'expired' => [fn () => ['status' => OrderStatus::Expired, 'expires_at' => now()->subDay()], false],
]);

test('guests cannot open the order history', function () {
    $this->get(route('orders.index'))->assertRedirect(route('login'));
});

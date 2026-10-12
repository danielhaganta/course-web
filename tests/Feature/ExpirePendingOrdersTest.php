<?php

use App\Enums\OrderStatus;
use App\Models\Order;

test('command expires overdue pending orders only', function () {
    $overdue = Order::factory()->create(['expires_at' => now()->subMinute()]);
    $stillValid = Order::factory()->create(['expires_at' => now()->addHour()]);
    $paidAfterDeadline = Order::factory()->paid()->create(['expires_at' => now()->subDay()]);
    $failed = Order::factory()->create(['status' => OrderStatus::Failed, 'expires_at' => now()->subDay()]);

    $this->artisan('orders:expire')
        ->expectsOutput('1 order ditandai kedaluwarsa.')
        ->assertSuccessful();

    expect($overdue->refresh()->status)->toBe(OrderStatus::Expired)
        ->and($stillValid->refresh()->status)->toBe(OrderStatus::Pending)
        ->and($paidAfterDeadline->refresh()->status)->toBe(OrderStatus::Paid)
        ->and($failed->refresh()->status)->toBe(OrderStatus::Failed);
});

test('command is scheduled every fifteen minutes', function () {
    $this->artisan('schedule:list')
        ->expectsOutputToContain('*/15 * * * *  php artisan orders:expire')
        ->assertSuccessful();
});

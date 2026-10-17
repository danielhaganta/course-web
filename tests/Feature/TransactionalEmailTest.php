<?php

use App\Models\Course;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Notifications\PaymentReceipt;
use App\Notifications\ResetPasswordNotification;
use App\Notifications\SubscriptionReminder;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Carbon;

test('emails are queued and sent in English', function () {
    $user = User::factory()->create();

    expect(new VerifyEmailNotification)->toBeInstanceOf(ShouldQueue::class)
        ->and(new ResetPasswordNotification('token'))->toBeInstanceOf(ShouldQueue::class)
        ->and(new PaymentReceipt(Order::factory()->create()))->toBeInstanceOf(ShouldQueue::class);

    $verify = (new VerifyEmailNotification)->toMail($user);
    $reset = (new ResetPasswordNotification('token'))->toMail($user);
    $html = (string) $verify->render();

    expect($verify->subject)->toBe('Verify your Poiema Academy email')
        ->and($reset->subject)->toBe('Reset your Poiema Academy password')
        ->and($html)->toContain('Verify email')
        ->toContain('having trouble clicking')
        ->toContain('Poiema Academy');
});

test('plan receipt shows the order and the subscription end date', function () {
    $plan = Plan::factory()->premium()->create(['name' => 'Premium Monthly', 'price' => 249_000]);
    $order = Order::factory()->paid()->create([
        'purchasable_id' => $plan->id,
        'amount' => 249_000,
        'payment_type' => 'qris',
        'paid_at' => Carbon::parse('2026-10-12 13:00', 'UTC'),
    ]);
    $subscription = Subscription::factory()->for($plan)->create(['ends_at' => Carbon::parse('2026-11-11 13:00', 'UTC')]);

    $mail = (new PaymentReceipt($order, $subscription))->toMail($order->user);
    $html = (string) $mail->render();

    expect($mail->subject)->toBe('Payment received: Premium Monthly')
        ->and($html)->toContain($order->code)
        ->toContain('Premium Monthly plan')
        ->toContain('Rp')
        ->toContain('249.000')
        ->toContain('QRIS')
        ->toContain('12 Oct 2026, 20:00 WIB')
        ->toContain('11 Nov 2026, 20:00 WIB')
        ->toContain(route('dashboard'));
});

test('course receipt mentions lifetime access', function () {
    $course = Course::factory()->published()->create(['title' => 'Color Grading Basics', 'price' => 299_000]);
    $order = Order::factory()->forCourse($course)->paid()->create();

    $html = (string) (new PaymentReceipt($order))->toMail($order->user)->render();

    expect($html)->toContain('Color Grading Basics course')
        ->toContain('lifetime access');
});

test('reminder lists what premium members lose', function () {
    $subscription = Subscription::factory()->for(Plan::factory()->premium()->create(['name' => 'Premium Monthly']))->create();

    $mail = (new SubscriptionReminder($subscription, 7))->toMail($subscription->user);
    $html = (string) $mail->render();

    expect($mail->subject)->toBe('Your Premium Monthly subscription ends in 7 days')
        ->and($html)->toContain('Access to all courses')
        ->toContain('Premium-only exclusive lessons')
        ->toContain('Practice file downloads')
        ->toContain(route('pricing'));
});

test('reminder subject uses the singular for one day left', function () {
    $subscription = Subscription::factory()->for(Plan::factory()->create(['name' => 'Standard Monthly']))->create();

    $mail = (new SubscriptionReminder($subscription, 1))->toMail($subscription->user);

    expect($mail->subject)->toBe('Your Standard Monthly subscription ends in 1 day');
});

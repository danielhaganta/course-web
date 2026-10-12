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

beforeEach(function () {
    // Saat dikirim, locale diambil dari User::preferredLocale(). Di sini email dirender langsung.
    app()->setLocale('id');
});

test('emails are queued and sent in Indonesian', function () {
    $user = User::factory()->create();

    expect($user->preferredLocale())->toBe('id')
        ->and(new VerifyEmailNotification)->toBeInstanceOf(ShouldQueue::class)
        ->and(new ResetPasswordNotification('token'))->toBeInstanceOf(ShouldQueue::class)
        ->and(new PaymentReceipt(Order::factory()->create()))->toBeInstanceOf(ShouldQueue::class);

    $verify = (new VerifyEmailNotification)->toMail($user);
    $reset = (new ResetPasswordNotification('token'))->toMail($user);
    $html = (string) $verify->render();

    expect($verify->subject)->toBe('Verifikasi email akun Poeima Academy')
        ->and($reset->subject)->toBe('Reset password akun Poeima Academy')
        ->and($html)->toContain('Verifikasi email')
        ->toContain('Kalau tombol')
        ->toContain('Poeima Academy');
});

test('plan receipt shows the order and the subscription end date', function () {
    $plan = Plan::factory()->premium()->create(['name' => 'Premium Bulanan', 'price' => 249_000]);
    $order = Order::factory()->paid()->create([
        'purchasable_id' => $plan->id,
        'amount' => 249_000,
        'payment_type' => 'qris',
        'paid_at' => Carbon::parse('2026-10-12 13:00', 'UTC'),
    ]);
    $subscription = Subscription::factory()->for($plan)->create(['ends_at' => Carbon::parse('2026-11-11 13:00', 'UTC')]);

    $mail = (new PaymentReceipt($order, $subscription))->toMail($order->user);
    $html = (string) $mail->render();

    expect($mail->subject)->toBe('Pembayaran berhasil: Premium Bulanan')
        ->and($html)->toContain($order->code)
        ->toContain('Paket Premium Bulanan')
        ->toContain('249.000')
        ->toContain('QRIS')
        ->toContain('12 Oktober 2026, 20.00 WIB')
        ->toContain('11 November 2026, 20.00 WIB')
        ->toContain(route('dashboard'));
});

test('course receipt mentions lifetime access', function () {
    $course = Course::factory()->published()->create(['title' => 'Color Grading Dasar', 'price' => 299_000]);
    $order = Order::factory()->forCourse($course)->paid()->create();

    $html = (string) (new PaymentReceipt($order))->toMail($order->user)->render();

    expect($html)->toContain('Course Color Grading Dasar')
        ->toContain('akses selamanya');
});

test('reminder lists what premium members lose', function () {
    $subscription = Subscription::factory()->for(Plan::factory()->premium()->create(['name' => 'Premium Bulanan']))->create();

    $mail = (new SubscriptionReminder($subscription, 7))->toMail($subscription->user);
    $html = (string) $mail->render();

    expect($mail->subject)->toBe('Langganan Premium Bulanan kamu berakhir dalam 7 hari')
        ->and($html)->toContain('Akses ke semua course')
        ->toContain('Materi exclusive')
        ->toContain('Download file latihan')
        ->toContain(route('pricing'));
});

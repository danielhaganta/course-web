<?php

use App\Models\Plan;
use App\Models\Subscription;
use App\Notifications\SubscriptionReminder;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    Notification::fake();
    // Command dijadwalkan pukul 09:00 WIB
    $this->travelTo(Carbon::parse('2026-10-12 09:00', 'Asia/Jakarta')->utc());
});

function subscriptionEndingAt(string $endsAtWib, array $attributes = []): Subscription
{
    return Subscription::factory()->create([
        'starts_at' => now()->subMonth(),
        // Simpan dalam UTC seperti data asli, Eloquent tidak mengonversi timezone
        'ends_at' => Carbon::parse($endsAtWib, 'Asia/Jakarta')->utc(),
        ...$attributes,
    ]);
}

function assertReminderSent(Subscription $subscription, int $daysLeft): void
{
    Notification::assertSentToTimes($subscription->user, SubscriptionReminder::class, 1);
    Notification::assertSentTo($subscription->user, SubscriptionReminder::class, fn (SubscriptionReminder $notification) => $notification->daysLeft === $daysLeft
        && $notification->subscription->is($subscription));
}

test('7 day and 1 day reminders are sent on the right day and only once', function () {
    $sevenDays = subscriptionEndingAt('2026-10-19 20:00');
    $oneDay = subscriptionEndingAt('2026-10-13 07:30');
    $threeDays = subscriptionEndingAt('2026-10-15 12:00');

    $this->artisan('subscriptions:send-reminders')->assertSuccessful();
    $this->artisan('subscriptions:send-reminders')->assertSuccessful();

    assertReminderSent($sevenDays, 7);
    assertReminderSent($oneDay, 1);
    Notification::assertNotSentTo($threeDays->user, SubscriptionReminder::class);

    expect($sevenDays->refresh()->reminder_7_days_sent_at)->not->toBeNull()
        ->and($oneDay->refresh()->reminder_1_day_sent_at)->not->toBeNull();
});

test('reminder is not sent when the user has already renewed', function () {
    $plan = Plan::factory()->create();
    $expiring = subscriptionEndingAt('2026-10-19 20:00', ['plan_id' => $plan->id]);
    subscriptionEndingAt('2026-11-18 20:00', ['user_id' => $expiring->user_id, 'plan_id' => $plan->id]);

    $this->artisan('subscriptions:send-reminders')->assertSuccessful();

    Notification::assertNothingSent();
    expect($expiring->refresh()->reminder_7_days_sent_at)->toBeNull();
});

test('ended notice is sent on the day the subscription ends', function () {
    $endsToday = subscriptionEndingAt('2026-10-12 20:00');
    $endedYesterday = subscriptionEndingAt('2026-10-11 20:00');

    $this->artisan('subscriptions:send-reminders')->assertSuccessful();

    assertReminderSent($endsToday, 0);
    Notification::assertNotSentTo($endedYesterday->user, SubscriptionReminder::class);
    expect($endsToday->refresh()->ended_notice_sent_at)->not->toBeNull();
});

test('reminders are scheduled daily at 09:00 WIB', function () {
    $event = collect(app(Schedule::class)->events())
        ->first(fn (Event $event) => str_contains((string) $event->command, 'subscriptions:send-reminders'));

    expect($event->expression)->toBe('0 9 * * *')
        ->and($event->timezone)->toBe('Asia/Jakarta');
});

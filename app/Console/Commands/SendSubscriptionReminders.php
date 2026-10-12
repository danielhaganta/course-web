<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Notifications\SubscriptionReminder;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Query\Builder;

#[Signature('subscriptions:send-reminders')]
#[Description('Kirim pengingat 7 hari dan 1 hari sebelum langganan berakhir, serta email di hari langganan berakhir')]
class SendSubscriptionReminders extends Command
{
    // Sisa hari => kolom penanda terkirim. 0 = hari langganan berakhir
    private const array REMINDERS = [
        7 => 'reminder_7_days_sent_at',
        1 => 'reminder_1_day_sent_at',
        0 => 'ended_notice_sent_at',
    ];

    public function handle(): int
    {
        // Hari dihitung dalam WIB, ends_at disimpan dalam UTC
        $today = now('Asia/Jakarta')->startOfDay();
        $sentCount = 0;

        foreach (self::REMINDERS as $daysLeft => $sentAtColumn) {
            $day = $today->addDays($daysLeft);

            Subscription::query()
                ->with(['user', 'plan'])
                ->whereNull($sentAtColumn)
                ->whereBetween('ends_at', [$day->utc(), $day->endOfDay()->utc()])
                // Sudah memperpanjang: punya langganan lain yang berakhir lebih lama
                ->whereNotExists(fn (Builder $query) => $query
                    ->from('subscriptions as renewal')
                    ->whereColumn('renewal.user_id', 'subscriptions.user_id')
                    ->whereColumn('renewal.ends_at', '>', 'subscriptions.ends_at'))
                // Per id, bukan offset: baris yang sudah ditandai keluar dari filter whereNull
                ->eachById(function (Subscription $subscription) use ($daysLeft, $sentAtColumn, &$sentCount): void {
                    $subscription->user->notify(new SubscriptionReminder($subscription, $daysLeft));
                    $subscription->update([$sentAtColumn => now()]);
                    $sentCount++;
                });
        }

        $this->info("{$sentCount} email pengingat langganan dikirim.");

        return self::SUCCESS;
    }
}

<?php

namespace App\Notifications;

use App\Models\Subscription;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Pengingat 7 dan 1 hari sebelum berakhir, serta pemberitahuan di hari langganan berakhir ($daysLeft = 0).
 */
class SubscriptionReminder extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Subscription $subscription,
        public int $daysLeft,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(User $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(User $notifiable): MailMessage
    {
        $plan = $this->subscription->plan;
        $endsAt = $this->formatDate($this->subscription->ends_at);

        $mail = (new MailMessage)
            ->greeting("Halo {$notifiable->name},");

        $mail = $this->daysLeft === 0
            ? $mail->subject("Langganan {$plan->name} kamu berakhir hari ini")
                ->line("Langganan **{$plan->name}** kamu berakhir hari ini, **{$endsAt}**.")
            : $mail->subject("Langganan {$plan->name} kamu berakhir dalam {$this->daysLeft} hari")
                ->line("Langganan **{$plan->name}** kamu akan berakhir pada **{$endsAt}**.");

        $mail->line('Kalau tidak diperpanjang, kamu akan kehilangan:')
            ->line('• Akses ke semua course');

        if ($plan->can_access_exclusive) {
            $mail->line('• Materi exclusive khusus Premium');
        }

        if ($plan->can_download) {
            $mail->line('• Download file latihan');
        }

        return $mail
            ->line('Course yang kamu beli satuan tetap bisa diakses.')
            ->action('Perpanjang langganan', route('pricing'));
    }

    private function formatDate(CarbonInterface $date): string
    {
        return $date->setTimezone('Asia/Jakarta')->locale('id')->translatedFormat('j F Y, H.i').' WIB';
    }
}

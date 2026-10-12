<?php

namespace App\Notifications;

use App\Models\Subscription;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

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
            ->greeting("Hi {$notifiable->name},");

        $mail = $this->daysLeft === 0
            ? $mail->subject("Your {$plan->name} subscription ends today")
                ->line("Your **{$plan->name}** subscription ends today, **{$endsAt}**.")
            : $mail->subject("Your {$plan->name} subscription ends in {$this->daysLeft} ".Str::plural('day', $this->daysLeft))
                ->line("Your **{$plan->name}** subscription ends on **{$endsAt}**.");

        $mail->line('If you don\'t renew, you will lose:')
            ->line('• Access to all courses');

        if ($plan->can_access_exclusive) {
            $mail->line('• Premium-only exclusive lessons');
        }

        if ($plan->can_download) {
            $mail->line('• Practice file downloads');
        }

        return $mail
            ->line('Courses you bought individually stay available.')
            ->action('Renew subscription', route('pricing'));
    }

    private function formatDate(CarbonInterface $date): string
    {
        return $date->setTimezone('Asia/Jakarta')->format('j M Y, H:i').' WIB';
    }
}

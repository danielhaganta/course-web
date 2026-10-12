<?php

namespace App\Notifications;

use App\Models\Order;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Services\Midtrans;
use Carbon\CarbonInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Number;

class PaymentReceipt extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  Subscription|null  $subscription  Diisi untuk pembelian paket
     */
    public function __construct(
        public Order $order,
        public ?Subscription $subscription = null,
    ) {
        // Masuk antrean hanya kalau transaksi aktivasi berhasil di-commit
        $this->afterCommit();
    }

    /**
     * @return array<int, string>
     */
    public function via(User $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(User $notifiable): MailMessage
    {
        $order = $this->order;
        $item = $order->purchasable;

        $mail = (new MailMessage)
            ->subject("Payment received: {$order->itemName()}")
            ->greeting("Hi {$notifiable->name},")
            ->line('Thanks, we have received your payment. Here are the details.')
            ->line("**Order code:** {$order->code}")
            ->line('**Item:** '.($item instanceof Plan ? "{$item->name} plan" : "{$item->title} course"))
            ->line('**Amount:** '.Number::currency($order->amount, in: 'IDR', locale: 'id', precision: 0))
            ->line('**Payment method:** '.(app(Midtrans::class)->paymentMethodLabel($order) ?? '-'))
            ->line('**Paid on:** '.($order->paid_at ? $this->formatDate($order->paid_at) : '-'));

        $mail = $item instanceof Plan
            ? $mail->line("Your **{$item->name}** subscription is active until **".($this->subscription ? $this->formatDate($this->subscription->ends_at) : '-').'**.')
            : $mail->line("You now have lifetime access to **{$item->title}**.");

        return $mail->action('Open dashboard', route('dashboard'));
    }

    private function formatDate(CarbonInterface $date): string
    {
        return $date->setTimezone('Asia/Jakarta')->format('j M Y, H:i').' WIB';
    }
}

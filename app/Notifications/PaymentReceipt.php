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
            ->subject("Pembayaran berhasil: {$order->itemName()}")
            ->greeting("Halo {$notifiable->name},")
            ->line('Terima kasih, pembayaran kamu sudah kami terima. Berikut detailnya.')
            ->line("**Kode order:** {$order->code}")
            ->line('**Item:** '.($item instanceof Plan ? "Paket {$item->name}" : "Course {$item->title}"))
            ->line('**Nominal:** '.Number::currency($order->amount, in: 'IDR', locale: 'id', precision: 0))
            ->line('**Metode pembayaran:** '.(app(Midtrans::class)->paymentMethodLabel($order) ?? '-'))
            ->line('**Tanggal bayar:** '.($order->paid_at ? $this->formatDate($order->paid_at) : '-'));

        $mail = $item instanceof Plan
            ? $mail->line("Langganan **{$item->name}** kamu aktif sampai **".($this->subscription ? $this->formatDate($this->subscription->ends_at) : '-').'**.')
            : $mail->line("Kamu sekarang punya akses selamanya ke course **{$item->title}**.");

        return $mail->action('Buka dashboard', route('dashboard'));
    }

    private function formatDate(CarbonInterface $date): string
    {
        return $date->setTimezone('Asia/Jakarta')->locale('id')->translatedFormat('j F Y, H.i').' WIB';
    }
}

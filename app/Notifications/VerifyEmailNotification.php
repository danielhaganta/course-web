<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Config;

class VerifyEmailNotification extends VerifyEmail implements ShouldQueue
{
    use Queueable;

    /**
     * @param  string  $url
     */
    protected function buildMailMessage($url): MailMessage
    {
        return (new MailMessage)
            ->subject('Verifikasi email akun Poeima Academy')
            ->line('Terima kasih sudah mendaftar di Poeima Academy. Klik tombol di bawah untuk memverifikasi alamat email kamu.')
            ->action('Verifikasi email', $url)
            ->line('Link ini berlaku '.Config::integer('auth.verification.expire', 60).' menit. Kalau kamu tidak merasa mendaftar, abaikan email ini.');
    }
}

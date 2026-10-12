<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Config;

class ResetPasswordNotification extends ResetPassword implements ShouldQueue
{
    use Queueable;

    /**
     * @param  string  $url
     */
    protected function buildMailMessage($url): MailMessage
    {
        $broker = Config::string('auth.defaults.passwords');

        return (new MailMessage)
            ->subject('Reset password akun Poeima Academy')
            ->line('Kami menerima permintaan untuk mengganti password akun Poeima Academy kamu.')
            ->action('Reset password', $url)
            ->line('Link ini berlaku '.Config::integer("auth.passwords.{$broker}.expire").' menit.')
            ->line('Kalau kamu tidak meminta reset password, abaikan email ini. Password kamu tidak akan berubah.');
    }
}

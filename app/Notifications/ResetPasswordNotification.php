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
            ->subject('Reset your Poiema Academy password')
            ->line('We received a request to reset the password for your Poiema Academy account.')
            ->action('Reset password', $url)
            ->line('This link expires in '.Config::integer("auth.passwords.{$broker}.expire").' minutes.')
            ->line('If you didn\'t request a password reset, you can ignore this email. Your password won\'t change.');
    }
}

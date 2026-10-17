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
            ->subject('Verify your Poiema Academy email')
            ->line('Thanks for signing up for Poiema Academy. Click the button below to verify your email address.')
            ->action('Verify email', $url)
            ->line('This link expires in '.Config::integer('auth.verification.expire', 60).' minutes. If you didn\'t sign up, you can ignore this email.');
    }
}

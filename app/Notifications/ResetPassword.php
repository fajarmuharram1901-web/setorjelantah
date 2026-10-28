<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPassword extends BaseResetPassword
{
    public function toMail($notifiable)
    {
        $url = $this->resetUrl($notifiable);

        return (new MailMessage)
            ->subject('Reset Password Akun SetorJelantah')
            ->view('emails.reset-password', [
                'url' => $url,
                'name' => $notifiable->name,
                'email' => $notifiable->email,
                'expireMinutes' => config('auth.passwords.' . config('auth.defaults.passwords') . '.expire'),
            ]);
    }
}
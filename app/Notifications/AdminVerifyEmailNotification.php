<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;

class AdminVerifyEmailNotification extends VerifyEmail
{
    protected function verificationUrl($notifiable): string
    {
        return URL::temporarySignedRoute(
            'admin.verification.verify',
            Carbon::now()->addMinutes(60),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Xác thực email Admin')
            ->line('Vui lòng xác thực email để kích hoạt tài khoản admin.')
            ->action('Xác thực email', $this->verificationUrl($notifiable))
            ->line('Nếu bạn không tạo tài khoản này, hãy bỏ qua email.');
    }
}

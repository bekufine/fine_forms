<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class VerifyEmail extends Notification implements ShouldQueue
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('【'.config('app.name').'】メールアドレスの確認')
            ->greeting($notifiable->name.' 様')
            ->line('以下のボタンをクリックして、メールアドレスの確認を完了してください。')
            ->action('メールアドレスを確認する', $url)
            ->line('このリンクの有効期限は60分です。')
            ->line('心当たりがない場合は、このメールを破棄してください。');
    }

    protected function verificationUrl(object $notifiable): string
    {
        // Signed against the API route so it can be validated without an
        // active session — this is what lets the link work as a deep link,
        // opened straight from an email client on any device.
        return URL::temporarySignedRoute(
            'api.verification.verify',
            now()->addMinutes(60),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }
}

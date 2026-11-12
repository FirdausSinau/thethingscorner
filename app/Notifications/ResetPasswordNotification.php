<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    public function __construct(public readonly string $token) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Build the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        return (new MailMessage)
            ->subject('Reset password akun Thethingscorner')
            ->greeting('Halo '.$notifiable->name.',')
            ->line('Kami menerima permintaan untuk mengganti password akun Thethingscorner kamu.')
            ->action('Buat password baru', $url)
            ->line('Link ini berlaku 60 menit. Abaikan email ini jika kamu tidak meminta reset password.');
    }
}

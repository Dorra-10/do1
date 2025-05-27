<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class SendNewUserCredentials extends Notification
{
    public $password;

    public function __construct($password)
    {
        $this->password = $password;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $token = \Password::createToken($notifiable);
        $resetUrl = url(route('password.reset', [
            'token' => $token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));
    
        return (new MailMessage)
        ->subject('Your account has been successfully created')
        ->greeting('Good Morning ' . $notifiable->name . ',')
        ->line('An account has been created for you on our application.')
        ->line('Here are your login details:')
        ->line('Email : ' . $notifiable->email)
        ->line('Password : ' . $this->password)
        ->action('Reset Password', $resetUrl)
        ->line('👉 [Login](' . url('/login') . ')')
        ->line('This link will expire in 60 minutes.');

}
}
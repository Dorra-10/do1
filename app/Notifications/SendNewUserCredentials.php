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
        return (new MailMessage)
            ->subject('Votre compte a été créé avec succès')
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line('Un compte a été créé pour vous sur notre application.')
            ->line('Voici vos identifiants de connexion :')
            ->line('**Email :** ' . $notifiable->email)
            ->line('**Mot de passe :** ' . $this->password)
            ->action('Se connecter', url('/login'));
          
    }
}

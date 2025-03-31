<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ClienteDesbloqueado extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('¡Acceso restaurado!')
            ->greeting('Hola ' . $notifiable->nombre . ' 👋')
            ->line('Te informamos que tu acceso al gimnasio ha sido restaurado con éxito.')
            ->line('Gracias por ponerte al día con tu pago. Ahora puedes ingresar normalmente.')
            ->action('Ver detalles', url('/')) // puedes enlazar al panel si gustas
            ->line('¡Nos alegra tenerte de vuelta!');
    }
}

<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;


class RecordatorioPagoNotification extends Notification
{
    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Recordatorio de Pago - Gimnasio')
            ->greeting('Hola ' . $notifiable->nombre)
            ->line('Tienes un saldo pendiente de Bs. ' . number_format($notifiable->saldo, 2))
            ->line('Tienes 5 días hábiles para regularizar tu situación.')
            ->line('De lo contrario, no podrás hacer uso de los servicios.')
            ->action('Contáctanos', url('/'))
            ->salutation('Gracias por tu preferencia.');
    }
}

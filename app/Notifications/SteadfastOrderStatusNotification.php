<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SteadfastOrderStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Order $order,
        private readonly string $status,
        private readonly array $payload = []
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Order Status Updated: ' . $this->order->transaction_number)
            ->greeting('Hello ' . ($this->order->customer_name ?: 'Customer') . ',')
            ->line('Your order status has been updated by Steadfast.')
            ->line('Order ID: ' . $this->order->transaction_number)
            ->line('Current Status: ' . $this->status)
            ->when(filled($this->order->steadfast_consignment_id), function (MailMessage $mail) {
                return $mail->line('Consignment ID: ' . $this->order->steadfast_consignment_id);
            })
            ->action('Track Order', route('front.order.track'))
            ->line('Thank you for shopping with us.');
    }
}

<?php

namespace App\Jobs;

use App\Models\Order;
use App\Notifications\SteadfastOrderStatusNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class SendSteadfastStatusEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $orderId,
        public string $status,
        public array $payload = []
    ) {
    }

    public function handle(): void
    {
        $order = Order::find($this->orderId);

        if (! $order || blank($order->customer_email)) {
            return;
        }

        Log::info('Sending Steadfast status email.', [
            'order_id' => $order->id,
            'email' => $order->customer_email,
            'status' => $this->status,
        ]);

        Notification::sendNow(
            Notification::route('mail', $order->customer_email),
            new SteadfastOrderStatusNotification($order, $this->status, $this->payload)
        );
    }
}

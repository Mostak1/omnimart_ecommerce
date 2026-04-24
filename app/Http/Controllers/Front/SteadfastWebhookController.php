<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Jobs\SendSteadfastStatusEmailJob;
use App\Models\Order;
use App\Models\Setting;
use App\Models\TrackOrder;
use App\Services\SteadfastService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SteadfastWebhookController extends Controller
{
    public function __invoke(Request $request, SteadfastService $steadfast)
    {
        $payload = $request->all();
        Log::info('Steadfast webhook received.', ['payload' => $payload]);

        if (! $this->passesWebhookAuth($request, $steadfast)) {
            Log::warning('Steadfast webhook authentication failed.');

            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $reference = $payload['ConsignmentReference'] ?? $payload['consignment_id'] ?? $payload['invoice'] ?? null;
        if (! $reference) {
            return response()->json(['message' => 'Missing consignment reference'], 422);
        }

        $order = Order::query()
            ->where('steadfast_consignment_id', $reference)
            ->orWhere('transaction_number', $reference)
            ->first();

        if (! $order) {
            Log::warning('Steadfast webhook order not found.', ['reference' => $reference]);

            return response()->json(['message' => 'Order not found'], 404);
        }

        $order = $steadfast->syncTrackingIntoOrder($order, $payload, 'webhook');
        $this->syncTrackOrderRows($order);

        if (filled($order->customer_email)) {
            if (Setting::find(1)?->is_queue_enabled == 1) {
                SendSteadfastStatusEmailJob::dispatch($order->id, $order->order_status, $payload);
            } else {
                (new SendSteadfastStatusEmailJob($order->id, $order->order_status, $payload))->handle();
            }
        }

        return response()->json(['message' => 'Webhook processed']);
    }

    private function passesWebhookAuth(Request $request, SteadfastService $steadfast): bool
    {
        $configuredToken = $steadfast->webhookToken();

        if (! filled($configuredToken)) {
            return true;
        }

        $providedToken = $request->bearerToken()
            ?: $request->header('X-Steadfast-Webhook-Token')
            ?: $request->query('token');

        return hash_equals($configuredToken, (string) $providedToken);
    }

    private function syncTrackOrderRows(Order $order): void
    {
        if (! TrackOrder::whereOrderId($order->id)->whereTitle('Pending')->exists()) {
            TrackOrder::create([
                'title' => 'Pending',
                'order_id' => $order->id,
            ]);
        }

        if (in_array($order->order_status, ['In Progress', 'Delivered', 'Canceled', 'Returned'], true)
            && ! TrackOrder::whereOrderId($order->id)->whereTitle('In Progress')->exists()) {
            TrackOrder::create([
                'title' => 'In Progress',
                'order_id' => $order->id,
            ]);
        }

        if ($order->order_status === 'Delivered' && ! TrackOrder::whereOrderId($order->id)->whereTitle('Delivered')->exists()) {
            TrackOrder::create([
                'title' => 'Delivered',
                'order_id' => $order->id,
            ]);
        }

        if ($order->order_status === 'Canceled' && ! TrackOrder::whereOrderId($order->id)->whereTitle('Canceled')->exists()) {
            TrackOrder::create([
                'title' => 'Canceled',
                'order_id' => $order->id,
            ]);
        }

        if ($order->order_status === 'Returned' && ! TrackOrder::whereOrderId($order->id)->whereTitle('Returned')->exists()) {
            TrackOrder::create([
                'title' => 'Returned',
                'order_id' => $order->id,
            ]);
        }
    }
}

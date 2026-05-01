<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Setting;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SteadfastService
{
    private const DEFAULT_BASE_URL = 'https://portal.packzy.com/api/v1';

    public function isConfigured(): bool
    {
        return filled($this->apiKey()) && filled($this->secretKey());
    }

    public function createOrder(Order $order): array
    {
        $payload = $this->buildCreateOrderPayload($order);

        $response = $this->request()
            ->post($this->baseUrl() . '/create_order', $payload);

        return $this->handleResponse($response, 'create_order', [
            'order_id' => $order->id,
            'transaction_number' => $order->transaction_number,
            'payload' => $payload,
        ]);
    }

    public function trackByConsignmentId(string $consignmentId): array
    {
        $response = $this->request()->get($this->baseUrl() . '/status_by_cid/' . urlencode($consignmentId));

        return $this->handleResponse($response, 'status_by_cid', [
            'consignment_id' => $consignmentId,
        ]);
    }

    public function syncTrackingIntoOrder(Order $order, array $trackingData, string $source = 'tracking'): Order
    {
        $deliveryStatus = strtolower((string) data_get($trackingData, 'delivery_status', data_get($trackingData, 'TripStatus', '')));
        $mappedStatus = $this->mapCourierStatusToOrderStatus($deliveryStatus);

        $order->forceFill([
            'steadfast_delivery_status' => $deliveryStatus ?: data_get($trackingData, 'TripStatus'),
            'steadfast_last_tracking_response' => json_encode($trackingData),
            'order_status' => $mappedStatus ?: $order->order_status,
        ])->save();

        Log::info('Steadfast tracking synced into order.', [
            'order_id' => $order->id,
            'source' => $source,
            'delivery_status' => $deliveryStatus,
            'mapped_status' => $mappedStatus,
        ]);

        return $order->fresh();
    }

    public function mapCourierStatusToOrderStatus(?string $status): ?string
    {
        $status = strtolower(trim((string) $status));

        return match ($status) {
            'delivered', 'partial_delivered', 'partially_delivered', 'completed', 'ended' => 'Delivered',
            'cancelled', 'canceled' => 'Canceled',
            'returned', 'partial_returned' => 'Returned',
            'booked', 'pending' => 'Pending',
            'allocated', 'in_progress', 'in progress', 'hold', 'received_at_hub', 'delivered_approval_pending' => 'In Progress',
            default => null,
        };
    }

    public function webhookToken(): ?string
    {
        $setting = Setting::find(1);

        return $setting->steadfast_webhook_token ?: env('STEADFAST_WEBHOOK_TOKEN');
    }

    public function buildCreateOrderPayload(Order $order): array
    {
        $billing = $order->billing_data;
        $shipping = $order->shipping_data;
        $recipientName = trim((string) ($shipping['ship_first_name'] ?? $billing['bill_first_name'] ?? 'Customer'));
        $recipientPhone = $this->normalizePhone((string) ($shipping['ship_phone'] ?? $billing['bill_phone'] ?? ''));

        $addressParts = array_filter([
            $shipping['ship_address1'] ?? $billing['bill_address1'] ?? null,
            $shipping['ship_address2'] ?? $billing['bill_address2'] ?? null,
            $shipping['ship_thana'] ?? $billing['bill_thana'] ?? null,
            $shipping['ship_country'] ?? $billing['bill_country'] ?? null,
        ]);

        $paymentStatus = strtolower((string) $order->payment_status);
        $codAmount = $paymentStatus === 'paid' ? 0 : (float) $order->total_amount;

        return [
            'invoice' => $order->transaction_number,
            'recipient_name' => $recipientName,
            'recipient_phone' => $recipientPhone,
            'recipient_address' => implode(', ', $addressParts),
            'cod_amount' => $codAmount,
            'note' => 'Order #' . $order->transaction_number . ' | Payment: ' . $order->payment_status,
        ];
    }

    private function request(): PendingRequest
    {
        return Http::acceptJson()
            ->asJson()
            ->timeout(30)
            ->withHeaders([
                'api-key' => $this->apiKey(),
                'secret-key' => $this->secretKey(),
            ]);
    }

    private function handleResponse($response, string $action, array $context = []): array
    {
        try {
            $response->throw();
        } catch (RequestException $exception) {
            Log::error('Steadfast API request failed.', [
                'action' => $action,
                'context' => $context,
                'status' => optional($exception->response)->status(),
                'body' => optional($exception->response)->body(),
            ]);

            throw $exception;
        }

        $data = $response->json();

        Log::info('Steadfast API request completed.', [
            'action' => $action,
            'context' => $context,
            'response' => $data,
        ]);

        return is_array($data) ? $data : [];
    }

    private function baseUrl(): string
    {
        $setting = Setting::find(1);

        return rtrim($setting->steadfast_base_url ?: env('STEADFAST_BASE_URL', self::DEFAULT_BASE_URL), '/');
    }

    private function apiKey(): ?string
    {
        $setting = Setting::find(1);

        return $setting->steadfast_api_key ?: env('STEADFAST_API_KEY');
    }

    private function secretKey(): ?string
    {
        $setting = Setting::find(1);

        return $setting->steadfast_secret_key ?: env('STEADFAST_SECRET_KEY');
    }

    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone);

        if (! $digits) {
            return '';
        }

        if (str_starts_with($digits, '880')) {
            return '0' . substr($digits, 3);
        }

        if (! str_starts_with($digits, '0')) {
            return '0' . ltrim($digits, '0');
        }

        return $digits;
    }
}

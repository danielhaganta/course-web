<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Plan;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Semua kode khusus Midtrans ada di sini. Ganti class ini saja kalau pindah payment gateway.
 * Hasil notifikasi dinormalisasi ke array PaymentResult supaya kode lain tidak tahu format Midtrans.
 *
 * @phpstan-type PaymentResult array{order_code: string, amount: int, status: OrderStatus|null, payment_type: string|null, payload: array<string, mixed>}
 */
class Midtrans
{
    // Kartu kredit belum diaktifkan. echannel = Mandiri Bill Payment (VA Mandiri di Snap)
    public const array ENABLED_PAYMENTS = [
        'other_qris',
        'gopay',
        'shopeepay',
        'bca_va',
        'bni_va',
        'bri_va',
        'permata_va',
        'echannel',
    ];

    // Batas panjang nama item dari Midtrans
    private const int ITEM_NAME_MAX_LENGTH = 50;

    public function createSnapToken(Order $order): string
    {
        $item = $order->purchasable;
        $user = $order->user;

        $response = $this->client()
            ->post($this->snapBaseUrl().'/snap/v1/transactions', [
                'transaction_details' => [
                    'order_id' => $order->code,
                    'gross_amount' => $order->amount,
                ],
                'item_details' => [[
                    'id' => "{$item->getMorphClass()}-{$item->getKey()}",
                    'price' => $order->amount,
                    'quantity' => 1,
                    'name' => Str::limit($item instanceof Plan ? $item->name : $item->title, self::ITEM_NAME_MAX_LENGTH, ''),
                ]],
                'customer_details' => [
                    'first_name' => $user->name,
                    'email' => $user->email,
                ],
                'enabled_payments' => self::ENABLED_PAYMENTS,
                // Masa berlaku sama dengan order
                'expiry' => [
                    'start_time' => $order->created_at?->setTimezone('Asia/Jakarta')->format('Y-m-d H:i:s O'),
                    'unit' => 'minutes',
                    'duration' => (int) $order->created_at?->diffInMinutes($order->expires_at),
                ],
                'callbacks' => [
                    'finish' => route('checkout.finish', $order),
                ],
            ])
            ->throw();

        return $response->json('token');
    }

    /**
     * Notifikasi webhook. Null kalau signature tidak valid.
     *
     * @param  array<string, mixed>  $payload
     * @return PaymentResult|null
     */
    public function parseNotification(array $payload): ?array
    {
        $expectedSignature = hash('sha512', implode('', [
            $payload['order_id'] ?? '',
            $payload['status_code'] ?? '',
            $payload['gross_amount'] ?? '',
            $this->serverKey(),
        ]));

        if (! is_string($payload['signature_key'] ?? null) || ! hash_equals($expectedSignature, $payload['signature_key'])) {
            return null;
        }

        return $this->normalize($payload);
    }

    /**
     * Get Status API. Data diambil langsung dari Midtrans dengan server key, jadi tidak perlu cek signature.
     * Null kalau transaksi belum ada (user belum memilih metode pembayaran).
     *
     * @return PaymentResult|null
     */
    public function fetchStatus(Order $order): ?array
    {
        /** @var array<string, mixed> $payload */
        $payload = $this->client()
            ->get($this->apiBaseUrl()."/v2/{$order->code}/status")
            ->throw()
            ->json();

        if (($payload['status_code'] ?? null) === '404') {
            return null;
        }

        return $this->normalize($payload);
    }

    /**
     * Nama metode pembayaran untuk ditampilkan, dari payment_type dan isi notifikasi terakhir.
     */
    public function paymentMethodLabel(Order $order): ?string
    {
        $payload = $order->gateway_payload ?? [];

        return match ($order->payment_type) {
            null => null,
            'bank_transfer' => match (true) {
                isset($payload['va_numbers'][0]['bank']) => Str::upper($payload['va_numbers'][0]['bank']).' Virtual Account',
                isset($payload['permata_va_number']) => 'Permata Virtual Account',
                default => 'Virtual Account',
            },
            'echannel' => 'Mandiri Bill',
            'qris' => 'QRIS',
            'gopay' => 'GoPay',
            'shopeepay' => 'ShopeePay',
            'credit_card' => 'Credit card',
            default => Str::headline($order->payment_type),
        };
    }

    /**
     * @return array{snap_js_url: string, client_key: string}
     */
    public function frontendConfig(): array
    {
        return [
            'snap_js_url' => $this->snapBaseUrl().'/snap/snap.js',
            'client_key' => $this->config('client_key'),
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return PaymentResult
     */
    private function normalize(array $payload): array
    {
        return [
            'order_code' => (string) ($payload['order_id'] ?? ''),
            // "149000.00" -> 149000
            'amount' => (int) round((float) ($payload['gross_amount'] ?? 0)),
            'status' => $this->orderStatus($payload),
            'payment_type' => isset($payload['payment_type']) ? (string) $payload['payment_type'] : null,
            'payload' => $payload,
        ];
    }

    /**
     * Null = status order tidak berubah (pending, atau status lain yang tidak ditangani).
     *
     * @param  array<string, mixed>  $payload
     */
    private function orderStatus(array $payload): ?OrderStatus
    {
        $fraudStatus = $payload['fraud_status'] ?? 'accept';

        return match ($payload['transaction_status'] ?? null) {
            'settlement' => OrderStatus::Paid,
            // capture hanya untuk kartu, sukses kalau lolos fraud detection
            'capture' => $fraudStatus === 'accept' ? OrderStatus::Paid : null,
            'expire' => OrderStatus::Expired,
            'deny', 'cancel', 'failure' => OrderStatus::Failed,
            default => null,
        };
    }

    private function client(): PendingRequest
    {
        return Http::withBasicAuth($this->serverKey(), '')
            ->acceptJson()
            ->timeout(15);
    }

    private function serverKey(): string
    {
        return $this->config('server_key');
    }

    // Key Midtrans bisa null kalau .env belum diisi
    private function config(string $key): string
    {
        $value = Config::get("services.midtrans.{$key}");

        return is_string($value) ? $value : '';
    }

    private function isProduction(): bool
    {
        return Config::boolean('services.midtrans.is_production');
    }

    private function snapBaseUrl(): string
    {
        return $this->isProduction() ? 'https://app.midtrans.com' : 'https://app.sandbox.midtrans.com';
    }

    private function apiBaseUrl(): string
    {
        return $this->isProduction() ? 'https://api.midtrans.com' : 'https://api.sandbox.midtrans.com';
    }
}

<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Course;
use App\Models\Order;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Checkout dan penerapan hasil pembayaran. Format data khusus Midtrans ditangani di class Midtrans.
 *
 * @phpstan-import-type PaymentResult from Midtrans
 */
class OrderService
{
    private const int ORDER_TTL_HOURS = 24;

    public function __construct(
        private Midtrans $midtrans,
        private OrderActivation $activation,
    ) {}

    /**
     * Pakai ulang order pending yang belum kedaluwarsa untuk item yang sama, supaya tidak ada tagihan ganda.
     * Harga selalu dari database.
     */
    public function checkout(User $user, Plan|Course $item): Order
    {
        $pendingOrder = Order::query()
            ->whereBelongsTo($user)
            ->whereMorphedTo('purchasable', $item)
            ->where('status', OrderStatus::Pending)
            ->where('expires_at', '>', now())
            ->whereNotNull('snap_token')
            ->latest('id')
            ->first();

        if ($pendingOrder) {
            return $pendingOrder;
        }

        $order = Order::create([
            'code' => Order::generateCode(),
            'user_id' => $user->id,
            'purchasable_type' => $item->getMorphClass(),
            'purchasable_id' => $item->getKey(),
            'amount' => $item->price,
            'status' => OrderStatus::Pending,
            'expires_at' => now()->addHours(self::ORDER_TTL_HOURS),
        ]);

        try {
            $order->update(['snap_token' => $this->midtrans->createSnapToken($order)]);
        } catch (Throwable $exception) {
            // Belum ada tagihan di gateway, order tidak perlu disimpan
            $order->delete();

            throw $exception;
        }

        return $order;
    }

    /**
     * Dipakai webhook dan cek status manual. Aman dipanggil berulang untuk notifikasi yang sama.
     *
     * @param  PaymentResult  $result
     * @return bool false kalau nominal tidak cocok dan hasil tidak diproses
     */
    public function applyPaymentResult(Order $order, array $result): bool
    {
        if ($result['amount'] !== $order->amount) {
            Log::warning('Pembayaran ditolak: nominal tidak cocok', [
                'order' => $order->code,
                'order_amount' => $order->amount,
                'gateway_amount' => $result['amount'],
            ]);

            return false;
        }

        DB::transaction(function () use ($order, $result): void {
            // Lock supaya notifikasi ganda yang datang bersamaan diproses bergantian
            $order = Order::query()->lockForUpdate()->findOrFail($order->id);

            // Order yang sudah dibayar tidak boleh berubah lagi
            if ($order->status === OrderStatus::Paid) {
                return;
            }

            $order->fill([
                'payment_type' => $result['payment_type'] ?? $order->payment_type,
                'gateway_payload' => $result['payload'],
            ]);

            if ($result['status'] === OrderStatus::Paid) {
                $order->fill(['status' => OrderStatus::Paid, 'paid_at' => now()])->save();
                $this->activation->activate($order);

                return;
            }

            if ($result['status'] !== null) {
                $order->status = $result['status'];
            }

            $order->save();
        });

        return true;
    }

    /**
     * Cek status langsung ke gateway, lalu diproses sama seperti webhook.
     */
    public function syncStatus(Order $order): void
    {
        $result = $this->midtrans->fetchStatus($order);

        if ($result !== null) {
            $this->applyPaymentResult($order, $result);
        }
    }
}

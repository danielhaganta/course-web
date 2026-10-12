<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\Midtrans;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    /**
     * Riwayat pembayaran milik user yang login.
     */
    public function index(Request $request, Midtrans $midtrans): Response
    {
        $orders = $request->user()->orders()
            ->with('purchasable')
            ->latest('id')
            ->get()
            ->map(fn (Order $order): array => [
                'code' => $order->code,
                'item_name' => $order->itemName(),
                'amount' => $order->amount,
                'payment_method' => $midtrans->paymentMethodLabel($order),
                'status' => $order->status,
                'status_label' => $order->status->getLabel(),
                'created_at' => $order->created_at?->toIso8601String(),
                // Snap token dipakai ulang untuk "Lanjutkan pembayaran"
                'payment' => $order->isPayable() && $order->snap_token ? [
                    'snap_token' => $order->snap_token,
                    'finish_url' => route('checkout.finish', $order),
                ] : null,
            ]);

        return Inertia::render('orders/index', [
            'orders' => $orders,
            'snap' => $midtrans->frontendConfig(),
        ]);
    }
}

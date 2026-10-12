<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\Midtrans;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class MidtransWebhookController extends Controller
{
    /**
     * Akses hanya diaktifkan dari sini atau dari cek status di server, tidak dari callback frontend.
     */
    public function __invoke(Request $request, Midtrans $midtrans, OrderService $orders): Response
    {
        $result = $midtrans->parseNotification($request->all());

        if ($result === null) {
            Log::warning('Notifikasi Midtrans ditolak: signature tidak valid', [
                'order_id' => $request->input('order_id'),
                'ip' => $request->ip(),
            ]);

            return response('Invalid signature', 403);
        }

        $order = Order::firstWhere('code', $result['order_code']);

        // Tetap 200: "Test notification URL" di dashboard Midtrans mengirim order_id yang tidak ada
        if (! $order) {
            Log::info('Notifikasi Midtrans untuk order yang tidak ada', ['order_id' => $result['order_code']]);

            return response('OK');
        }

        if (! $orders->applyPaymentResult($order, $result)) {
            return response('Amount mismatch', 422);
        }

        return response('OK');
    }
}

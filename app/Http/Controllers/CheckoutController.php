<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\Course;
use App\Models\Order;
use App\Models\Plan;
use App\Models\User;
use App\Services\Midtrans;
use App\Services\OrderService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    public function __construct(
        private OrderService $orders,
        private Midtrans $midtrans,
    ) {}

    public function plan(Request $request, Plan $plan): JsonResponse
    {
        abort_unless($plan->is_active, 404);

        return $this->startCheckout($request->user(), $plan);
    }

    public function course(Request $request, Course $course): JsonResponse
    {
        abort_unless($course->is_published && $course->price !== null, 404);

        if ($request->user()->coursePurchases()->where('course_id', $course->id)->exists()) {
            throw ValidationException::withMessages(['course' => 'Kamu sudah memiliki course ini.']);
        }

        return $this->startCheckout($request->user(), $course);
    }

    public function finish(Request $request, Order $order): Response
    {
        $this->ensureOwner($request, $order);

        $item = $order->purchasable;

        return Inertia::render('checkout/finish', [
            'order' => [
                'code' => $order->code,
                'status' => $order->status,
                'amount' => $order->amount,
                'expires_at' => $order->expires_at->toIso8601String(),
                'item' => [
                    'type' => $item->getMorphClass(),
                    'name' => $item instanceof Plan ? $item->name : $item->title,
                    'slug' => $item->slug,
                ],
            ],
        ]);
    }

    /**
     * Tombol "Cek status pembayaran" untuk order yang masih pending.
     */
    public function refreshStatus(Request $request, Order $order): RedirectResponse
    {
        $this->ensureOwner($request, $order);

        if ($order->status === OrderStatus::Pending) {
            try {
                $this->orders->syncStatus($order);
            } catch (ConnectionException|RequestException $exception) {
                Log::error('Gagal cek status pembayaran', ['order' => $order->code, 'error' => $exception->getMessage()]);
                Inertia::flash('toast', ['type' => 'error', 'message' => 'Status belum bisa dicek. Coba lagi beberapa saat.']);

                return back();
            }
        }

        Inertia::flash('toast', $order->refresh()->status === OrderStatus::Pending
            ? ['type' => 'info', 'message' => 'Pembayaran belum diterima.']
            : ['type' => 'success', 'message' => 'Status pembayaran sudah diperbarui.']);

        return back();
    }

    private function startCheckout(User $user, Plan|Course $item): JsonResponse
    {
        try {
            $order = $this->orders->checkout($user, $item);
        } catch (ConnectionException|RequestException $exception) {
            Log::error('Gagal membuat transaksi pembayaran', ['user' => $user->id, 'error' => $exception->getMessage()]);

            return response()->json(['message' => 'Pembayaran belum bisa disiapkan. Coba lagi beberapa saat.'], 502);
        }

        return response()->json([
            'snap_token' => $order->snap_token,
            'finish_url' => route('checkout.finish', $order),
            ...$this->midtrans->frontendConfig(),
        ]);
    }

    private function ensureOwner(Request $request, Order $order): void
    {
        abort_unless($order->user_id === $request->user()->id, 403);
    }
}

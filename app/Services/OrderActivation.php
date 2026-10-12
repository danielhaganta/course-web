<?php

namespace App\Services;

use App\Models\Course;
use App\Models\CoursePurchase;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Subscription;

// Memberi akses setelah order dibayar. Tidak tahu apa pun soal payment gateway.
class OrderActivation
{
    public function activate(Order $order): void
    {
        $item = $order->purchasable;

        match (true) {
            $item instanceof Plan => $this->activatePlan($order, $item),
            $item instanceof Course => CoursePurchase::firstOrCreate(['user_id' => $order->user_id, 'course_id' => $item->id]),
        };
    }

    /**
     * Plan sama yang masih aktif diperpanjang dari tanggal berakhirnya, selain itu langganan baru mulai sekarang.
     */
    private function activatePlan(Order $order, Plan $plan): void
    {
        $current = $order->user->activeSubscriptions()
            ->where('plan_id', $plan->id)
            ->latest('ends_at')
            ->first();

        if ($current) {
            $current->update([
                'ends_at' => $current->ends_at->addDays($plan->duration_days),
                // Langganan manual yang diperpanjang lewat pembayaran ikut ditandai berbayar
                'order_id' => $current->order_id ?? $order->id,
            ]);

            return;
        }

        Subscription::create([
            'user_id' => $order->user_id,
            'plan_id' => $plan->id,
            'order_id' => $order->id,
            'starts_at' => now(),
            'ends_at' => now()->addDays($plan->duration_days),
        ]);
    }
}

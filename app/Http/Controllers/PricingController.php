<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Services\DeviceSessions;
use Inertia\Inertia;
use Inertia\Response;

class PricingController extends Controller
{
    public function index(DeviceSessions $devices): Response
    {
        $plans = Plan::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Plan $plan): array => $plan->only([
                'id', 'name', 'slug', 'description', 'price', 'duration_days', 'can_access_exclusive', 'can_download',
            ]));

        return Inertia::render('pricing', [
            'plans' => $plans,
            // Untuk tabel perbandingan, batas yang sama berlaku di semua paket
            'maxDevices' => $devices->maxDevices(),
        ]);
    }
}

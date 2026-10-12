<?php

namespace App\Http\Controllers;

use App\Models\CoursePurchase;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        // Kalau ada beberapa yang aktif, tampilkan yang paling lama berlaku
        $subscription = $user->activeSubscriptions()
            ->with('plan')
            ->latest('ends_at')
            ->first();

        $purchasedCourses = $user->coursePurchases()
            ->whereRelation('course', 'is_published', true)
            ->with('course')
            ->latest()
            ->get()
            ->map(fn (CoursePurchase $purchase): array => $purchase->course->only(['title', 'slug']));

        return Inertia::render('dashboard', [
            'subscription' => $subscription ? [
                'plan_name' => $subscription->plan->name,
                'ends_at' => $subscription->ends_at->toIso8601String(),
            ] : null,
            'purchasedCourses' => $purchasedCourses,
        ]);
    }
}

<?php

use App\Models\CoursePurchase;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});

test('dashboard shows the active subscription and purchased courses', function () {
    $subscription = Subscription::factory()->for(Plan::factory()->premium())->create();
    Subscription::factory()->expired()->for($subscription->user)->create();
    $purchase = CoursePurchase::factory()->for($subscription->user)->create();

    $this->actingAs($subscription->user)
        ->get(route('dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('dashboard')
            ->where('subscription.plan_name', 'Premium Bulanan')
            ->where('subscription.ends_at', $subscription->ends_at->toIso8601String())
            ->has('purchasedCourses', 1)
            ->where('purchasedCourses.0.slug', $purchase->course->slug));
});

test('dashboard shows no subscription when it has expired', function () {
    $subscription = Subscription::factory()->expired()->create();

    $this->actingAs($subscription->user)
        ->get(route('dashboard'))
        ->assertInertia(fn (Assert $page) => $page->where('subscription', null));
});

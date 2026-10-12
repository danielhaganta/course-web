<?php

use App\Models\Plan;
use Inertia\Testing\AssertableInertia as Assert;

test('pricing page only shows active plans in sort order', function () {
    $second = Plan::factory()->premium()->create(['sort_order' => 2]);
    $first = Plan::factory()->create(['sort_order' => 1]);
    Plan::factory()->inactive()->create();

    $this->get(route('pricing'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('pricing')
            ->has('plans', 2)
            ->where('plans.0.id', $first->id)
            ->where('plans.1.id', $second->id));
});

test('pricing page sends the device limit for the comparison table', function () {
    config(['auth.max_devices' => 3]);

    $this->get(route('pricing'))
        ->assertInertia(fn (Assert $page) => $page->where('maxDevices', 3));
});

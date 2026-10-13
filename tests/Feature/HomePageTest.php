<?php

use Inertia\Testing\AssertableInertia as Assert;

test('home page sends the device limit for the FAQ', function () {
    config(['auth.max_devices' => 3]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('home')
            ->where('maxDevices', 3));
});

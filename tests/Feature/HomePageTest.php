<?php

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Section;
use Inertia\Testing\AssertableInertia as Assert;

test('home page sends the device limit for the FAQ', function () {
    config(['auth.max_devices' => 3]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('home')
            ->where('maxDevices', 3));
});

test('home page summarizes only published courses for the hero', function () {
    $published = Course::factory()->published()->create();
    Lesson::factory()->count(2)
        ->for(Section::factory()->for($published))
        ->create(['duration_seconds' => 600]);
    Lesson::factory()->for(Section::factory()->for(Course::factory()))->create();

    $this->get(route('home'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('catalog.courses', 1)
            ->where('catalog.lessons', 2)
            ->where('catalog.duration_seconds', 1200));
});

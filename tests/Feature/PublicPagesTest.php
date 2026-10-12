<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('public pages render their component', function (string $routeName, string $component) {
    $this->get(route($routeName))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component($component));
})->with([
    'beranda' => ['home', 'home'],
    'before / after' => ['before-after', 'before-after'],
    'pengajar' => ['instructor', 'instructor'],
    'syarat & ketentuan' => ['terms', 'info'],
    'kebijakan privasi' => ['privacy', 'info'],
    'kontak' => ['contact', 'info'],
]);

test('website is always rendered in dark mode', function () {
    $this->get(route('home'))->assertSee('<html lang="en" class="dark">', escape: false);
});

test('appearance settings are removed', function () {
    $this->actingAs(User::factory()->create())
        ->get('/settings/appearance')
        ->assertNotFound();
});

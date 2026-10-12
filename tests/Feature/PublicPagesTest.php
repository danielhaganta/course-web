<?php

use App\Models\Order;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('public pages render their component', function (string $routeName, string $component) {
    $this->get(route($routeName))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component($component));
})->with([
    'home' => ['home', 'home'],
    'before / after' => ['before-after', 'before-after'],
    'instructor' => ['instructor', 'instructor'],
    'terms & conditions' => ['terms', 'info'],
    'privacy policy' => ['privacy', 'info'],
    'contact' => ['contact', 'info'],
]);

test('website is always rendered in dark mode', function () {
    $this->get(route('home'))->assertSee('<html lang="en" class="dark">', escape: false);
});

test('appearance settings are removed', function () {
    $this->actingAs(User::factory()->create())
        ->get('/settings/appearance')
        ->assertNotFound();
});

test('footer pages show a clear placeholder', function (string $routeName, string $placeholder) {
    $this->get(route($routeName))
        ->assertInertia(fn (Assert $page) => $page
            ->component('info')
            ->where('placeholder', fn (string $text) => str_contains($text, $placeholder)));
})->with([
    'terms & conditions' => ['terms', '[Terms & conditions from the website owner'],
    'privacy policy' => ['privacy', '[Privacy policy from the website owner'],
    'contact' => ['contact', '[Contact details from the website owner'],
]);

test('public pages use English URLs', function (string $routeName, string $path) {
    expect(route($routeName, absolute: false))->toBe($path);
})->with([
    ['instructor', '/instructor'],
    ['terms', '/terms'],
    ['privacy', '/privacy'],
    ['contact', '/contact'],
]);

test('error pages use English messages and link back home', function () {
    $this->get('/page-that-does-not-exist')
        ->assertNotFound()
        ->assertSee('Page not found')
        ->assertSee('Back to home');
});

test('forbidden page uses the new style', function () {
    $order = Order::factory()->create();

    $this->actingAs(User::factory()->create())
        ->get(route('checkout.finish', $order))
        ->assertForbidden()
        ->assertSee('Access denied');
});

test('login errors are shown in English', function () {
    $user = User::factory()->create();

    $this->post(route('login.store'), ['email' => $user->email, 'password' => 'wrong-password'])
        ->assertSessionHasErrors(['email' => 'These credentials do not match our records.']);
});

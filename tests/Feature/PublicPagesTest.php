<?php

use App\Models\Order;
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

test('footer pages show a clear placeholder', function (string $routeName, string $placeholder) {
    $this->get(route($routeName))
        ->assertInertia(fn (Assert $page) => $page
            ->component('info')
            ->where('placeholder', fn (string $text) => str_contains($text, $placeholder)));
})->with([
    'syarat & ketentuan' => ['terms', '[Isi syarat & ketentuan dari pemilik website'],
    'kebijakan privasi' => ['privacy', '[Isi kebijakan privasi dari pemilik website'],
    'kontak' => ['contact', '[Isi kontak dari pemilik website'],
]);

test('error pages use Indonesian messages and link back home', function () {
    $this->get('/halaman-yang-tidak-ada')
        ->assertNotFound()
        ->assertSee('Halaman tidak ditemukan')
        ->assertSee('Kembali ke beranda');
});

test('forbidden page uses the new style', function () {
    $order = Order::factory()->create();

    $this->actingAs(User::factory()->create())
        ->get(route('checkout.finish', $order))
        ->assertForbidden()
        ->assertSee('Akses ditolak');
});

test('login errors are shown in Indonesian', function () {
    app()->setLocale('id');
    $user = User::factory()->create();

    $this->post(route('login.store'), ['email' => $user->email, 'password' => 'salah'])
        ->assertSessionHasErrors(['email' => 'Email atau password salah.']);
});

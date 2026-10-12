<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Support\SessionKey;

beforeEach(function () {
    config(['session.driver' => 'database', 'session.lifetime' => 120, 'auth.max_devices' => 2]);
});

function seedSession(User $user, string $id, int $minutesAgo): void
{
    DB::table('sessions')->insert([
        'id' => $id,
        'user_id' => $user->id,
        'ip_address' => '10.0.0.1',
        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/130.0 Safari/537.36',
        'payload' => '',
        'last_activity' => now()->subMinutes($minutesAgo)->getTimestamp(),
    ]);
}

/**
 * Tiap perangkat = sesi dan state auth baru, seperti browser lain.
 */
function switchDevice(): void
{
    app('session')->forgetDrivers();
    app()->forgetInstance('session.store');
    Auth::forgetGuards();
}

function sessionIdsOf(User $user): array
{
    return DB::table('sessions')->where('user_id', $user->id)->pluck('id')->all();
}

test('login on a third device logs out the least recently active session', function () {
    $user = User::factory()->create();
    seedSession($user, 'old-device', minutesAgo: 30);
    seedSession($user, 'recent-device', minutesAgo: 1);

    $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])
        ->assertRedirect()
        ->assertSessionHas(SessionKey::FLASH_DATA.'.toast.message', 'You signed in on a new device. Your older inactive device has been signed out.');

    $sessionIds = sessionIdsOf($user);
    expect($sessionIds)->toHaveCount(2)
        ->toContain('recent-device')
        ->not->toContain('old-device');
});

test('admin is not limited', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    seedSession($admin, 'device-a', minutesAgo: 30);
    seedSession($admin, 'device-b', minutesAgo: 1);

    $this->post(route('login.store'), ['email' => $admin->email, 'password' => 'password'])
        ->assertSessionMissing(SessionKey::FLASH_DATA);

    expect(sessionIdsOf($admin))->toHaveCount(3);
});

test('sessions past the session lifetime are not counted', function () {
    $user = User::factory()->create();
    seedSession($user, 'expired-device', minutesAgo: 125);
    seedSession($user, 'recent-device', minutesAgo: 1);

    $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])
        ->assertSessionMissing(SessionKey::FLASH_DATA);

    expect(sessionIdsOf($user))->toContain('expired-device', 'recent-device')->toHaveCount(3);
});

test('member can log out another device but not a session of another user', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    seedSession($user, 'my-other-device', minutesAgo: 5);
    seedSession($otherUser, 'someone-else', minutesAgo: 5);

    $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->get(route('devices.edit'))
        ->assertOk();

    $this->delete(route('devices.destroy', hash('sha256', 'someone-else')))->assertNotFound();
    $this->delete(route('devices.destroy', hash('sha256', 'my-other-device')))->assertRedirect();

    expect(sessionIdsOf($user))->not->toContain('my-other-device')
        ->and(sessionIdsOf($otherUser))->toContain('someone-else');
});

test('device actions require password confirmation', function () {
    $user = User::factory()->create();
    seedSession($user, 'my-other-device', minutesAgo: 5);

    $this->actingAs($user)
        ->delete(route('devices.destroy', hash('sha256', 'my-other-device')))
        ->assertRedirect(route('password.confirm'));

    expect(sessionIdsOf($user))->toContain('my-other-device');
});

test('logged out device cannot sign back in with its remember me cookie', function () {
    $user = User::factory()->create();
    $recallerName = Auth::guard('web')->getRecallerName();
    $login = fn () => $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password', 'remember' => 'on']);

    $oldDeviceCookie = $login()->getCookie($recallerName)->getValue();
    DB::table('sessions')->where('user_id', $user->id)->update(['last_activity' => now()->subMinutes(30)->getTimestamp()]);

    switchDevice();
    $login();
    switchDevice();
    $newDeviceCookie = $login()->getCookie($recallerName)->getValue();

    switchDevice();
    $this->withCookie($recallerName, $oldDeviceCookie)->get(route('dashboard'))->assertRedirect(route('login'));
    $this->assertGuest();

    // Perangkat yang baru login tetap bisa masuk lewat cookie-nya
    switchDevice();
    $this->withCookie($recallerName, $newDeviceCookie)->get(route('dashboard'))->assertOk();
});

<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Services\DeviceSessions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;
use stdClass;

class DeviceController extends Controller
{
    public function __construct(private DeviceSessions $devices) {}

    /**
     * Show the user's active devices.
     */
    public function edit(Request $request): Response
    {
        $user = $request->user();
        $currentSessionId = $request->session()->getId();

        $sessions = $this->devices->activeSessions($user)->map(fn (stdClass $session): array => [
            // Id sesi asli tidak dikirim ke browser, hanya hash-nya
            'key' => $this->sessionKey($session->id),
            ...$this->describeUserAgent($session->user_agent),
            'ip_address' => $session->ip_address,
            'last_active' => Carbon::createFromTimestamp($session->last_activity)
                ->settings(['locale' => 'id'])
                ->diffForHumans(),
            'is_current' => $session->id === $currentSessionId,
        ]);

        return Inertia::render('settings/devices', [
            'sessions' => $sessions,
            'maxDevices' => $user->is_admin ? null : $this->devices->maxDevices(),
        ]);
    }

    /**
     * Log out one of the user's other devices.
     */
    public function destroy(Request $request, string $key): RedirectResponse
    {
        $sessionId = $this->devices->activeSessions($request->user())
            ->pluck('id')
            ->first(fn (string $id): bool => $this->sessionKey($id) === $key);

        abort_if($sessionId === null, 404);
        abort_if($sessionId === $request->session()->getId(), 403);

        $this->devices->logoutSessions($request->user(), [$sessionId]);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Perangkat sudah dikeluarkan.']);

        return back();
    }

    /**
     * Log out all of the user's other devices.
     */
    public function destroyOthers(Request $request): RedirectResponse
    {
        $otherSessionIds = $this->devices->activeSessions($request->user())
            ->pluck('id')
            ->reject(fn (string $id): bool => $id === $request->session()->getId());

        $this->devices->logoutSessions($request->user(), $otherSessionIds->values()->all());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Semua perangkat lain sudah dikeluarkan.']);

        return back();
    }

    private function sessionKey(string $sessionId): string
    {
        return hash('sha256', $sessionId);
    }

    /**
     * Deteksi sederhana dari user agent. Urutan penting: Edge/Opera memuat "Chrome",
     * Chrome memuat "Safari", iPhone memuat "Mac OS", Android memuat "Linux".
     *
     * @return array{browser: string, platform: string}
     */
    private function describeUserAgent(?string $userAgent): array
    {
        $agent = $userAgent ?? '';

        $browser = match (true) {
            str_contains($agent, 'Edg/') => 'Edge',
            str_contains($agent, 'OPR/') => 'Opera',
            str_contains($agent, 'Firefox/') => 'Firefox',
            str_contains($agent, 'Chrome/') => 'Chrome',
            str_contains($agent, 'Safari/') => 'Safari',
            default => 'Browser tidak dikenal',
        };

        $platform = match (true) {
            str_contains($agent, 'Windows') => 'Windows',
            str_contains($agent, 'Android') => 'Android',
            str_contains($agent, 'iPhone'), str_contains($agent, 'iPad') => 'iOS',
            str_contains($agent, 'Mac OS') => 'macOS',
            str_contains($agent, 'Linux') => 'Linux',
            default => 'OS tidak dikenal',
        };

        return ['browser' => $browser, 'platform' => $platform];
    }
}

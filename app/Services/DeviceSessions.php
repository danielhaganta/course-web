<?php

namespace App\Services;

use App\Models\User;
use Closure;
use Illuminate\Auth\SessionGuard;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use stdClass;

// Satu-satunya tempat aturan batas perangkat dan pengeluaran sesi login
class DeviceSessions
{
    public function maxDevices(): int
    {
        return max(1, Config::integer('auth.max_devices'));
    }

    /**
     * Sesi aktif = last_activity masih dalam SESSION_LIFETIME. Terbaru dulu.
     *
     * @return Collection<int, stdClass>
     */
    public function activeSessions(User $user): Collection
    {
        return $this->activeSessionsQuery($user)
            ->orderByDesc('last_activity')
            ->get(['id', 'ip_address', 'user_agent', 'last_activity']);
    }

    /**
     * Dipanggil saat login. Sesi baru belum tersimpan di tabel saat event login,
     * jadi dikecualikan lewat id supaya tidak ikut dikeluarkan.
     *
     * @return int jumlah sesi yang dikeluarkan
     */
    public function enforceLimit(User $user, string $currentSessionId): int
    {
        if ($user->is_admin) {
            return 0;
        }

        // Satu slot untuk sesi baru, sisanya untuk sesi lain yang paling baru aktif
        $keptIds = $this->activeSessionsQuery($user)
            ->where('id', '!=', $currentSessionId)
            ->orderByDesc('last_activity')
            ->limit($this->maxDevices() - 1)
            ->pluck('id');

        $evictedIds = $this->activeSessionsQuery($user)
            ->where('id', '!=', $currentSessionId)
            ->whereNotIn('id', $keptIds)
            ->pluck('id');

        return $this->logoutSessions($user, $evictedIds->all());
    }

    /**
     * @param  array<int, string>  $sessionIds
     * @return int jumlah sesi yang dikeluarkan
     */
    public function logoutSessions(User $user, array $sessionIds): int
    {
        if ($sessionIds === []) {
            return 0;
        }

        // Filter user_id: tidak bisa mengeluarkan sesi milik user lain
        $deleted = DB::table(Config::string('session.table'))
            ->where('user_id', $user->id)
            ->whereIn('id', $sessionIds)
            ->delete();

        if ($deleted > 0) {
            $this->rotateRememberToken($user);
        }

        return $deleted;
    }

    /**
     * Token remember me dipakai bersama semua perangkat. Diganti supaya perangkat yang
     * dikeluarkan tidak login lagi lewat cookie, lalu cookie perangkat ini dipasang ulang.
     * Sama seperti Auth::logoutOtherDevices(), memakai method guard sendiri.
     */
    private function rotateRememberToken(User $user): void
    {
        $guard = Auth::guard('web');

        if (! $guard instanceof SessionGuard) {
            return;
        }

        Closure::bind(function () use ($user): void {
            $this->cycleRememberToken($user);

            if ($this->recaller() || $this->getCookieJar()->hasQueued($this->getRecallerName())) {
                $this->queueRecallerCookie($user);
            }
        }, $guard, SessionGuard::class)();
    }

    private function activeSessionsQuery(User $user): Builder
    {
        $lifetimeMinutes = Config::integer('session.lifetime');

        return DB::table(Config::string('session.table'))
            ->where('user_id', $user->id)
            ->where('last_activity', '>=', now()->subMinutes($lifetimeMinutes)->getTimestamp());
    }
}

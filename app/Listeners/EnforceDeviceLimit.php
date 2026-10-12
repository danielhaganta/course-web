<?php

namespace App\Listeners;

use App\Models\User;
use App\Services\DeviceSessions;
use Illuminate\Auth\Events\Login;
use Inertia\Inertia;

class EnforceDeviceLimit
{
    public function __construct(private DeviceSessions $devices) {}

    public function handle(Login $event): void
    {
        if (! $event->user instanceof User) {
            return;
        }

        $evictedCount = $this->devices->enforceLimit($event->user, session()->getId());

        if ($evictedCount > 0) {
            Inertia::flash('toast', [
                'type' => 'info',
                'message' => 'Kamu login di perangkat baru. Perangkat lama yang tidak aktif sudah dikeluarkan.',
            ]);
        }
    }
}

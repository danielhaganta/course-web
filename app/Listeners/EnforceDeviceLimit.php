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
                'message' => 'You signed in on a new device. Your older inactive device has been signed out.',
            ]);
        }
    }
}

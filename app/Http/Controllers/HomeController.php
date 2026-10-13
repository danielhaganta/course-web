<?php

namespace App\Http\Controllers;

use App\Services\DeviceSessions;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(DeviceSessions $devices): Response
    {
        return Inertia::render('home', [
            // Untuk jawaban FAQ jumlah perangkat
            'maxDevices' => $devices->maxDevices(),
        ]);
    }
}

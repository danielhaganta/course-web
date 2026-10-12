<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Subscription;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class MembershipStats extends StatsOverviewWidget
{
    protected static ?int $sort = -10;

    protected function getStats(): array
    {
        // Batas bulan mengikuti WIB, kolom paid_at disimpan dalam UTC
        $now = now('Asia/Jakarta');
        $thisMonthStart = $now->startOfMonth()->utc();
        $thisMonthEnd = $now->endOfMonth()->utc();
        $lastMonthStart = $now->subMonthNoOverflow()->startOfMonth()->utc();
        $lastMonthEnd = $now->subMonthNoOverflow()->endOfMonth()->utc();

        return [
            Stat::make('Revenue this month', $this->rupiah(Order::paidBetween($thisMonthStart, $thisMonthEnd)->sum('amount'))),
            Stat::make('Revenue last month', $this->rupiah(Order::paidBetween($lastMonthStart, $lastMonthEnd)->sum('amount'))),
            Stat::make('Paid orders this month', Order::paidBetween($thisMonthStart, $thisMonthEnd)->count()),
            Stat::make('Active subscribers', Subscription::active()->distinct()->count('user_id')),
        ];
    }

    private function rupiah(mixed $amount): string
    {
        return (string) Number::currency((int) $amount, in: 'IDR', locale: 'id', precision: 0);
    }
}

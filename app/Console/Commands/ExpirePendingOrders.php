<?php

namespace App\Console\Commands;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('orders:expire')]
#[Description('Ubah order pending yang sudah lewat batas waktu menjadi expired')]
class ExpirePendingOrders extends Command
{
    public function handle(): int
    {
        // Filter status di query yang sama, jadi order yang baru saja paid lewat webhook tidak tersentuh.
        // Kalau settlement datang setelah ini, webhook tetap boleh mengubah expired menjadi paid.
        $expiredCount = Order::query()
            ->where('status', OrderStatus::Pending)
            ->where('expires_at', '<=', now())
            ->update(['status' => OrderStatus::Expired]);

        $this->info("{$expiredCount} order ditandai kedaluwarsa.");

        return self::SUCCESS;
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            // Juga dipakai sebagai order_id di payment gateway
            $table->string('code')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // Plan atau Course
            $table->morphs('purchasable');
            $table->unsignedBigInteger('amount');
            // pending | paid | failed | expired
            $table->string('status')->default('pending');
            $table->string('payment_type')->nullable();
            $table->string('snap_token')->nullable();
            // Isi notifikasi gateway terakhir
            $table->json('gateway_payload')->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->dateTime('expires_at');
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

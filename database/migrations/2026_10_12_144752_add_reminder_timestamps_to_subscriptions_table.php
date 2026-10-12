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
        Schema::table('subscriptions', function (Blueprint $table) {
            // Penanda email pengingat sudah dikirim, supaya tiap jenis hanya terkirim sekali
            $table->dateTime('reminder_7_days_sent_at')->nullable()->after('ends_at');
            $table->dateTime('reminder_1_day_sent_at')->nullable()->after('reminder_7_days_sent_at');
            $table->dateTime('ended_notice_sent_at')->nullable()->after('reminder_1_day_sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn(['reminder_7_days_sent_at', 'reminder_1_day_sent_at', 'ended_notice_sent_at']);
        });
    }
};

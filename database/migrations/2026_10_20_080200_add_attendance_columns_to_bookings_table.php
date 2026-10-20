<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->unsignedInteger('participant_count')
                ->default(1)
                ->after('purpose');

            // Token yang di-encode ke QR; dibuat saat booking disetujui.
            $table->string('qr_token', 64)
                ->nullable()
                ->unique()
                ->after('status');

            // Pemakaian aktual (event QR check-in / check-out).
            $table->dateTime('checked_in_at')->nullable()->after('qr_token');
            $table->dateTime('checked_out_at')->nullable()->after('checked_in_at');

            // Mempercepat query deteksi bentrok jadwal.
            $table->index(['room_id', 'start_time', 'end_time']);
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex(['room_id', 'start_time', 'end_time']);
            $table->dropUnique(['qr_token']);
            $table->dropColumn([
                'participant_count',
                'qr_token',
                'checked_in_at',
                'checked_out_at',
            ]);
        });
    }
};

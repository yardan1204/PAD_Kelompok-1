<?php

namespace App\Strategies;

use App\Models\Room;
use App\Support\Utilization\UtilizationPeriod;
use App\Support\Utilization\UtilizationResult;

/**
 * Kontrak Strategy perhitungan utilisasi.
 *
 * Menambah tipe ruang baru = membuat kelas yang mengimplementasikan interface
 * ini + mendaftarkannya di AppServiceProvider (2 file).
 */
interface UtilizationStrategyInterface
{
    /**
     * Tipe ruang (rooms.type) yang ditangani Strategy ini. Kembalikan string
     * langsung (mis. 'auditorium'); tidak perlu menambah konstanta di Room.
     */
    public function roomType(): string;

    public function calculate(Room $room, UtilizationPeriod $period): UtilizationResult;
}

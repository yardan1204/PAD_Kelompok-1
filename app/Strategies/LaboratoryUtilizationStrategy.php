<?php

namespace App\Strategies;

use App\Models\Room;
use App\Support\Utilization\UtilizationPeriod;
use App\Support\Utilization\UtilizationResult;

/**
 * Laboratorium.
 *
 * utilization_rate = (jam aktual + jam setup/kalibrasi - penalti ghost)
 *                    / jam tersedia x 100
 *
 *  - Setup/kalibrasi : setiap sesi yang benar-benar dipakai memakan
 *                      SETUP_MINUTES untuk menyiapkan alat praktikum (dihitung
 *                      sebagai waktu terpakai).
 *  - Penalti ghost   : setiap jam yang ditahan ghost booking dikurangi
 *                      GHOST_PENALTY_WEIGHT jam, karena slot itu tertahan
 *                      tanpa dipakai dan merugikan peminjam lain.
 */
class LaboratoryUtilizationStrategy extends AbstractUtilizationStrategy
{
    public const SETUP_MINUTES = 30;
    public const GHOST_PENALTY_WEIGHT = 0.5;

    public const ROOM_TYPE = 'laboratory';

    public function roomType(): string
    {
        return self::ROOM_TYPE;
    }

    public function calculate(Room $room, UtilizationPeriod $period): UtilizationResult
    {
        $bookings = $this->approvedBookings($room, $period);
        $scheduled = $this->scheduledHours($room, $period, $bookings);
        $actual = $this->actualHours($bookings, $period);

        $usedSessions = $bookings->filter(
            fn ($booking) => $booking->checked_in_at !== null
        )->count();

        $setupHours = $usedSessions * self::SETUP_MINUTES / 60;

        $ghostHours = $bookings
            ->filter(fn ($booking) => $booking->isGhostBooking())
            ->sum(fn ($booking) => $period->operatingHoursWithin(
                $booking->start_time,
                $booking->end_time
            ));

        $penaltyHours = $ghostHours * self::GHOST_PENALTY_WEIGHT;

        $effectiveHours = max(0.0, $actual + $setupHours - $penaltyHours);

        return $this->buildResult(
            $room,
            $period,
            $bookings,
            $scheduled,
            $actual,
            $this->percentage($effectiveHours, $period->availableHours()),
            [
                'setup_hours' => round($setupHours, 2),
                'ghost_penalty_hours' => round($penaltyHours, 2),
                'raw_actual_rate' => $this->percentage(
                    $actual,
                    $period->availableHours()
                ),
            ]
        );
    }
}

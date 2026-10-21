<?php

namespace App\Strategies;

use App\Models\Room;
use App\Support\Utilization\UtilizationPeriod;
use App\Support\Utilization\UtilizationResult;

/**
 * Auditorium.
 *
 * Auditorium dipakai untuk acara besar yang jarang tetapi ramai, sehingga jam
 * pemakaian ditimbang dengan seberapa penuh kursinya.
 *
 * utilization_rate = jumlah (jam dipakai x peserta / kapasitas) / jam tersedia x 100
 */
class AuditoriumUtilizationStrategy extends AbstractUtilizationStrategy
{
    public const ROOM_TYPE = 'auditorium';

    public function roomType(): string
    {
        return self::ROOM_TYPE;
    }

    public function calculate(Room $room, UtilizationPeriod $period): UtilizationResult
    {
        $bookings = $this->approvedBookings($room, $period);
        $scheduled = $this->scheduledHours($room, $period, $bookings);
        $actual = $this->actualHours($bookings, $period);
        $capacity = $room->getCapacity();

        $seatHours = 0.0;
        foreach ($bookings as $booking) {
            if ($booking->checked_in_at === null || $capacity <= 0) {
                continue;
            }

            $out = $booking->checked_out_at ?? $booking->end_time;
            $hours = $period->operatingHoursWithin($booking->checked_in_at, $out);
            $seatHours += $hours * min(1, $booking->participant_count / $capacity);
        }

        return $this->buildResult(
            $room,
            $period,
            $bookings,
            $scheduled,
            $actual,
            $this->percentage($seatHours, $period->availableHours()),
            [
                'seat_hours' => round($seatHours, 2),
                'raw_actual_rate' => $this->percentage($actual, $period->availableHours()),
            ]
        );
    }
}

<?php

namespace App\Strategies;

use App\Models\Booking;
use App\Models\Room;
use App\Support\Utilization\UtilizationPeriod;
use App\Support\Utilization\UtilizationResult;
use Illuminate\Support\Collection;

/**
 * Inheritance: helper bersama (query jadwal/booking, jam terjadwal, jam aktual,
 * klasifikasi). Subclass hanya menentukan rumus utilisasinya sendiri.
 */
abstract class AbstractUtilizationStrategy implements UtilizationStrategyInterface
{
    // Ambang kategori (persen), mengacu contoh Project Report 1:
    // 12% = underutilized, 44% = normal, 88% = overutilized.
    protected const UNDERUTILIZED_BELOW = 30.0;
    protected const OVERUTILIZED_FROM = 80.0;

    /**
     * Booking disetujui (approved/checked_in/completed/no_show) yang
     * bersinggungan dengan periode.
     *
     * @return Collection<int, Booking>
     */
    protected function approvedBookings(
        Room $room,
        UtilizationPeriod $period
    ): Collection {
        return $room->bookings()
            ->whereIn('status', Booking::APPROVED_STATUSES)
            ->where('start_time', '<=', $period->end)
            ->where('end_time', '>=', $period->start)
            ->get();
    }

    /**
     * Jam terjadwal = jadwal (non-cancelled) + booking disetujui,
     * hanya yang jatuh di jam operasional.
     *
     * @param Collection<int, Booking> $bookings
     */
    protected function scheduledHours(
        Room $room,
        UtilizationPeriod $period,
        Collection $bookings
    ): float {
        $hours = 0.0;

        $schedules = $room->schedules()
            ->where('status', '!=', 'cancelled')
            ->where('start_time', '<=', $period->end)
            ->where('end_time', '>=', $period->start)
            ->get();

        foreach ($schedules as $schedule) {
            $hours += $period->operatingHoursWithin(
                $schedule->start_time,
                $schedule->end_time
            );
        }

        foreach ($bookings as $booking) {
            $hours += $period->operatingHoursWithin(
                $booking->start_time,
                $booking->end_time
            );
        }

        return $hours;
    }

    /**
     * Jam aktual = durasi check-in sampai check-out. Sesi yang belum check-out
     * dihitung sampai sekarang, maksimal sampai jam selesai booking.
     *
     * @param Collection<int, Booking> $bookings
     */
    protected function actualHours(
        Collection $bookings,
        UtilizationPeriod $period
    ): float {
        $hours = 0.0;
        $now = now();

        foreach ($bookings as $booking) {
            if ($booking->checked_in_at === null) {
                continue;
            }

            $out = $booking->checked_out_at
                ?? ($now->lt($booking->end_time) ? $now : $booking->end_time);

            $hours += $period->operatingHoursWithin($booking->checked_in_at, $out);
        }

        return $hours;
    }

    protected function percentage(float $part, float $whole): float
    {
        if ($whole <= 0) {
            return 0.0;
        }

        return round(min(100.0, max(0.0, $part / $whole * 100)), 1);
    }

    protected function classify(float $rate, UtilizationPeriod $period): string
    {
        if ($period->availableHours() <= 0) {
            return UtilizationResult::CATEGORY_NORMAL;
        }

        if ($rate < static::UNDERUTILIZED_BELOW) {
            return UtilizationResult::CATEGORY_UNDERUTILIZED;
        }

        if ($rate >= static::OVERUTILIZED_FROM) {
            return UtilizationResult::CATEGORY_OVERUTILIZED;
        }

        return UtilizationResult::CATEGORY_NORMAL;
    }

    /**
     * @param Collection<int, Booking> $bookings
     * @param array<string, float|int|null> $metrics
     */
    protected function buildResult(
        Room $room,
        UtilizationPeriod $period,
        Collection $bookings,
        float $scheduledHours,
        float $actualHours,
        float $utilizationRate,
        array $metrics = []
    ): UtilizationResult {
        $available = $period->availableHours();
        $approved = $bookings->count();
        $ghosts = $bookings->filter(
            fn (Booking $booking) => $booking->isGhostBooking()
        )->count();

        $rate = round(min(100.0, max(0.0, $utilizationRate)), 1);

        return new UtilizationResult(
            roomId: $room->id,
            roomCode: (string) $room->code,
            roomName: (string) $room->name,
            roomType: (string) $room->type,
            availableHours: round($available, 2),
            scheduledHours: round($scheduledHours, 2),
            actualHours: round($actualHours, 2),
            scheduledRate: $this->percentage($scheduledHours, $available),
            utilizationRate: $rate,
            approvedBookings: $approved,
            ghostBookings: $ghosts,
            ghostRate: $approved > 0 ? round($ghosts / $approved * 100, 1) : 0.0,
            category: $this->classify($rate, $period),
            metrics: $metrics,
        );
    }
}

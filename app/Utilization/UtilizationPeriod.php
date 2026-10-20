<?php

namespace App\Support\Utilization;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use InvalidArgumentException;

/**
 * Periode analisis utilisasi + definisi "jam tersedia".
 *
 * Sesuai Project Report 1: jam tersedia 07.00-17.00 = 10 jam x hari kerja
 * (Senin-Jumat), mis. satu minggu = 50 jam. Jam dihitung pada timezone aplikasi.
 */
final class UtilizationPeriod
{
    public const OPENING_HOUR = 7;
    public const CLOSING_HOUR = 17;

    public readonly CarbonImmutable $start;
    public readonly CarbonImmutable $end;

    /**
     * Batas "sekarang". Bila diisi, jam yang belum terjadi tidak dihitung
     * (jam tersedia, terjadwal, aktual), sehingga periode berjalan tidak
     * tampak underutilized hanya karena minggunya belum selesai.
     * Null = periode dihitung penuh (dipakai untuk periode yang sudah lewat).
     */
    public readonly ?CarbonImmutable $asOf;

    public function __construct(
        CarbonInterface $start,
        CarbonInterface $end,
        ?CarbonInterface $asOf = null
    ) {
        $this->asOf = $asOf !== null ? CarbonImmutable::instance($asOf) : null;
        $this->start = CarbonImmutable::instance($start)->startOfDay();
        $this->end = CarbonImmutable::instance($end)->endOfDay();

        if ($this->end->lt($this->start)) {
            throw new InvalidArgumentException(
                'Akhir periode tidak boleh sebelum awal periode.'
            );
        }
    }

    // Senin-Minggu pada minggu yang memuat $now.
    public static function currentWeek(?CarbonInterface $now = null): self
    {
        $now = CarbonImmutable::instance($now ?? now());

        return new self($now->startOfWeek(), $now->endOfWeek(), $now);
    }

    /** @return list<CarbonImmutable> hari kerja (Senin-Jumat) dalam periode */
    public function workingDays(): array
    {
        $days = [];

        for ($day = $this->start; $day->lte($this->end); $day = $day->addDay()) {
            if ($day->isWeekday()) {
                $days[] = $day;
            }
        }

        return $days;
    }

    public function availableHours(): float
    {
        if ($this->asOf === null) {
            return count($this->workingDays())
                * (self::CLOSING_HOUR - self::OPENING_HOUR);
        }

        // Hanya jam operasional yang sudah lewat sampai $asOf.
        return $this->operatingHoursWithin($this->start, $this->end);
    }

    /**
     * Jumlah jam dari rentang [$from, $to] yang jatuh di dalam jam operasional
     * pada hari kerja dalam periode ini.
     */
    public function operatingHoursWithin(
        CarbonInterface $from,
        CarbonInterface $to
    ): float {
        $seconds = 0;
        $limit = $this->asOf?->getTimestamp() ?? PHP_INT_MAX;

        foreach ($this->workingDays() as $day) {
            $windowStart = $day->setTime(self::OPENING_HOUR, 0)->getTimestamp();
            $windowEnd = $day->setTime(self::CLOSING_HOUR, 0)->getTimestamp();

            $overlapStart = max($from->getTimestamp(), $windowStart);
            $overlapEnd = min($to->getTimestamp(), $windowEnd, $limit);

            if ($overlapEnd > $overlapStart) {
                $seconds += $overlapEnd - $overlapStart;
            }
        }

        return $seconds / 3600;
    }
}

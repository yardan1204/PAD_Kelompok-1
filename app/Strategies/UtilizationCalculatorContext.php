<?php

namespace App\Strategies;

use App\Models\Room;
use App\Support\Utilization\UtilizationPeriod;
use App\Support\Utilization\UtilizationResult;
use Illuminate\Support\Collection;
use InvalidArgumentException;

/**
 * Context pada Strategy Pattern: memilih Strategy berdasarkan tipe ruang lalu
 * mendelegasikan perhitungan (polimorfik). Context tidak mengenal kelas
 * Strategy konkret, sehingga tipe ruang baru tidak mengubah kelas ini.
 */
class UtilizationCalculatorContext
{
    /** @var array<string, UtilizationStrategyInterface> */
    private array $strategies = [];

    /**
     * @param iterable<UtilizationStrategyInterface> $strategies
     */
    public function __construct(iterable $strategies)
    {
        foreach ($strategies as $strategy) {
            $this->strategies[$strategy->roomType()] = $strategy;
        }
    }

    public function strategyFor(Room $room): UtilizationStrategyInterface
    {
        $type = (string) $room->type;

        return $this->strategies[$type]
            ?? throw new InvalidArgumentException(
                "Belum ada strategi utilisasi untuk tipe ruang '{$type}'."
            );
    }

    public function calculate(Room $room, UtilizationPeriod $period): UtilizationResult
    {
        return $this->strategyFor($room)->calculate($room, $period);
    }

    /**
     * @param iterable<Room> $rooms
     * @return Collection<int, UtilizationResult>
     */
    public function calculateAll(iterable $rooms, UtilizationPeriod $period): Collection
    {
        return collect($rooms)->map(
            fn (Room $room) => $this->calculate($room, $period)
        )->values();
    }

    /** @return list<string> tipe ruang yang sudah punya Strategy */
    public function supportedTypes(): array
    {
        return array_keys($this->strategies);
    }
}

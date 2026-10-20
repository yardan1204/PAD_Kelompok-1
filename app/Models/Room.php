<?php

namespace App\Models;

use InvalidArgumentException;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends BaseCampusEntity
{
    private int $minimumCapacity = 1;

    private array $allowedStatuses = [
        'available',
        'occupied',
        'maintenance',
    ];

    protected $fillable = [
        'academic_unit_id',
        'code',
        'name',
        'building',
        'floor',
        'capacity',
        'status',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
        ];
    }

    // Relasi ke unit akademik
    public function academicUnit(): BelongsTo
    {
        return $this->belongsTo(AcademicUnit::class);
    }

    // Relasi ke fasilitas
    public function facilities(): HasMany
    {
        return $this->hasMany(Facility::class);
    }

    // Relasi ke jadwal
    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    // Relasi ke peminjaman
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    // Getter kapasitas
    public function getCapacity(): int
    {
        return (int) $this->getAttribute('capacity');
    }

    // Setter kapasitas
    public function setCapacity(int $capacity): void
    {
        $this->setAttribute('capacity', $capacity);
    }

    // Getter status
    public function getStatus(): string
    {
        return (string) $this->getAttribute('status');
    }

    // Setter status
    public function setStatus(string $status): void
    {
        $this->setAttribute('status', $status);
    }

    // Mutator: validasi kapasitas untuk seluruh assignment Eloquent
    protected function capacity(): Attribute
    {
        return Attribute::make(
            set: function ($value) {
                $value = filter_var(
                    $value,
                    FILTER_VALIDATE_INT
                );

                if (
                    $value === false ||
                    $value < $this->minimumCapacity
                ) {
                    throw new InvalidArgumentException(
                        'Kapasitas ruangan minimal 1 orang.'
                    );
                }

                return $value;
            }
        );
    }

    // Mutator: validasi status
    protected function status(): Attribute
    {
        return Attribute::make(
            set: function ($value) {
                if (!in_array(
                    $value,
                    $this->allowedStatuses,
                    true
                )) {
                    throw new InvalidArgumentException(
                        'Status ruangan tidak valid.'
                    );
                }

                return $value;
            }
        );
    }

    // Implementasi abstract method dan polymorphism
    public function getEntitySummary(): string
    {
        return "Ruangan: {$this->name}, "
            . "Kapasitas: {$this->getCapacity()} orang";
    }
}

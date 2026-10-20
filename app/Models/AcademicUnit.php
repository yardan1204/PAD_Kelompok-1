<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicUnit extends BaseCampusEntity
{
    protected $fillable = [
        'code',
        'name',
        'description',
    ];

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    public function getEntitySummary(): string
    {
        return "Unit Akademik: {$this->name} ({$this->code})";
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends BaseCampusEntity
{
    protected $fillable = [
        'room_id',
        'user_id',
        'purpose',
        'start_time',
        'end_time',
        'status',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time' => 'datetime',
        ];
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getEntitySummary(): string
    {
        return "Peminjaman: {$this->purpose}, Status: {$this->status}";
    }
}
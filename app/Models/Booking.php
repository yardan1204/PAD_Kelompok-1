<?php

namespace App\Models;

use App\Exceptions\BookingException;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use InvalidArgumentException;

class Booking extends BaseCampusEntity
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_CHECKED_IN = 'checked_in';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_NO_SHOW = 'no_show';

    public const APPROVED_STATUSES = [
        self::STATUS_APPROVED,
        self::STATUS_CHECKED_IN,
        self::STATUS_COMPLETED,
        self::STATUS_NO_SHOW,
    ];

    public const GHOST_TOLERANCE_MINUTES = 15;

    protected $fillable = [
        'room_id',
        'user_id',
        'requester_name',
        'requester_role',
        'purpose',
        'participant_count',
        'start_time',
        'end_time',
        'status',
        'qr_token',
        'checked_in_at',
        'checked_out_at',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'checked_in_at' => 'datetime',
            'checked_out_at' => 'datetime',
            'participant_count' => 'integer',
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

    public function getStatus(): string
    {
        return (string) $this->getAttribute('status');
    }

    public function setStatus(string $status): void
    {
        if (!in_array($status, [
            self::STATUS_PENDING,
            self::STATUS_APPROVED,
            self::STATUS_REJECTED,
            self::STATUS_CANCELLED,
            self::STATUS_CHECKED_IN,
            self::STATUS_COMPLETED,
            self::STATUS_NO_SHOW,
        ], true)) {
            throw new InvalidArgumentException('Status booking tidak valid.');
        }

        $this->setAttribute('status', $status);
    }

    public function isGhostBooking(?CarbonInterface $now = null): bool
    {
        if ($this->getStatus() === self::STATUS_NO_SHOW) {
            return true;
        }

        $start = $this->getAttribute('start_time');

        return $this->getStatus() === self::STATUS_APPROVED
            && $this->getAttribute('checked_in_at') === null
            && $start !== null
            && $start->copy()
                ->addMinutes(self::GHOST_TOLERANCE_MINUTES)
                ->lt($now ?? now());
    }

    public function detectAndMarkGhostBooking(?CarbonInterface $now = null): bool
    {
        if ($this->getStatus() !== self::STATUS_APPROVED) {
            return false;
        }

        if (!$this->isGhostBooking($now)) {
            return false;
        }

        $this->setStatus(self::STATUS_NO_SHOW);
        $this->save();

        return true;
    }

    public function hasScheduleConflict(): bool
    {
        $start = $this->getAttribute('start_time');
        $end = $this->getAttribute('end_time');

        if ($start === null || $end === null) {
            return false;
        }

        $overlapsSchedule = Schedule::query()
            ->where('room_id', $this->getAttribute('room_id'))
            ->where('status', '!=', 'cancelled')
            ->where('start_time', '<', $end)
            ->where('end_time', '>', $start)
            ->exists();

        if ($overlapsSchedule) {
            return true;
        }

        $bookings = static::query()
            ->where('room_id', $this->getAttribute('room_id'))
            ->whereIn('status', [
                self::STATUS_APPROVED,
                self::STATUS_CHECKED_IN,
            ])
            ->where('start_time', '<', $end)
            ->where('end_time', '>', $start);

        if ($this->exists) {
            $bookings->whereKeyNot($this->getKey());
        }

        return $bookings->exists();
    }

    public function performQrCheckIn(
        string $qrToken,
        ?CarbonInterface $at = null
    ): void {
        $this->assertQrToken($qrToken);

        if ($this->getStatus() !== self::STATUS_APPROVED) {
            throw BookingException::invalidState(
                'check-in',
                $this->getStatus()
            );
        }

        if ($this->getAttribute('checked_in_at') !== null) {
            throw BookingException::alreadyCheckedIn();
        }

        $at ??= now();

        if (
            $at->lt($this->start_time->copy()->subMinutes(15))
            || $at->gt($this->end_time)
        ) {
            throw BookingException::outsideCheckInWindow();
        }

        $this->setAttribute('checked_in_at', $at);
        $this->setStatus(self::STATUS_CHECKED_IN);
        $this->save();
    }

    public function performQrCheckOut(
        string $qrToken,
        ?CarbonInterface $at = null
    ): void {
        $this->assertQrToken($qrToken);

        if ($this->getStatus() !== self::STATUS_CHECKED_IN) {
            throw BookingException::invalidState(
                'check-out',
                $this->getStatus()
            );
        }

        if ($this->getAttribute('checked_out_at') !== null) {
            throw BookingException::alreadyCheckedOut();
        }

        $this->setAttribute('checked_out_at', $at ?? now());
        $this->setStatus(self::STATUS_COMPLETED);
        $this->save();
    }

    private function assertQrToken(string $qrToken): void
    {
        $stored = $this->getAttribute('qr_token');

        if ($stored === null || !hash_equals((string) $stored, $qrToken)) {
            throw BookingException::invalidQrToken();
        }
    }

    public function getEntitySummary(): string
    {
        return "Peminjaman: {$this->purpose}, Status: {$this->status}";
    }
}
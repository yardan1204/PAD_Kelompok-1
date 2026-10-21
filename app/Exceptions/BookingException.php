<?php

namespace App\Exceptions;

use RuntimeException;

class BookingException extends RuntimeException
{
    public static function notApprover(): self
    {
        return new self(
            'Hanya Kepala Laboratorium atau Admin Akademik yang dapat memverifikasi booking.'
        );
    }

    public static function invalidState(string $action, string $status): self
    {
        return new self(
            "Booking tidak dapat {$action} karena statusnya '{$status}'."
        );
    }

    public static function roomUnderMaintenance(): self
    {
        return new self('Ruangan sedang dalam perawatan.');
    }

    public static function capacityExceeded(
        int $participants,
        int $capacity
    ): self {
        return new self(
            "Jumlah peserta ({$participants}) melebihi kapasitas ruangan ({$capacity})."
        );
    }

    public static function scheduleConflict(): self
    {
        return new self(
            'Jadwal bentrok dengan jadwal atau booking lain di ruangan yang sama.'
        );
    }

    public static function invalidQrToken(): self
    {
        return new self('Token QR tidak valid.');
    }

    public static function alreadyCheckedIn(): self
    {
        return new self('Booking sudah melakukan check-in.');
    }

    public static function alreadyCheckedOut(): self
    {
        return new self('Booking sudah melakukan check-out.');
    }

    public static function outsideCheckInWindow(): self
    {
        return new self(
            'Check-in hanya diperbolehkan mulai 15 menit sebelum jadwal hingga waktu selesai.'
        );
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request untuk otorisasi persetujuan peminjaman ruangan.
 * Memastikan request dikirim oleh user yang terotentikasi.
 */
class ApproveBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Pengecekan peran spesifik (Kepala Lab / Admin) didelegasikan ke BookingService.
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [];
    }
}

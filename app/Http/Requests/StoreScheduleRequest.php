<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request untuk validasi pembuatan jadwal perkuliahan atau praktikum baru.
 * Memastikan ruangan valid dan waktu selesai harus lebih besar dari waktu mulai.
 */
class StoreScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Otorisasi terbuka untuk kebutuhan lingkungan pengembangan lokal.
        return true;
    }

    /**
     * Aturan validasi masukan form jadwal.
     */
    public function rules(): array
    {
        return [
            'room_id' => [
                'required',
                'integer',
                'exists:rooms,id',
            ],
            'title' => [
                'required',
                'string',
                'max:150',
            ],
            'start_time' => [
                'required',
                'date',
            ],
            'end_time' => [
                'required',
                'date',
                'after:start_time',
            ],
        ];
    }
}

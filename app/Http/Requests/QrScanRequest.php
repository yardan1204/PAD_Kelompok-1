<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Simulasi scan QR (check-in / check-out): isi QR adalah token booking.
 */
class QrScanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'qr_token' => [
                'required',
                'string',
                'max:64',
            ],
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApproveBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Pengecekan peran (Kepala Lab/Admin) dilakukan di BookingService.
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [];
    }
}

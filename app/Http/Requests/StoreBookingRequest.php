<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'requester_name' => [
                'required',
                'string',
                'max:150',
            ],
            'requester_role' => [
                'required',
                'string',
                'max:100',
            ],
            'room_id' => [
                'required',
                'integer',
                'exists:rooms,id',
            ],
            'participant_count' => [
                'required',
                'integer',
                'min:1',
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
            'purpose' => [
                'required',
                'string',
                'max:255',
            ],
        ];
    }
}

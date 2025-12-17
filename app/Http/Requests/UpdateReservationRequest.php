<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReservationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'space_id'   => 'required|exists:spaces,id',
            'event_name' => 'sometimes|string|max:255',
            'start_time' => 'sometimes|date|after:now',
            'end_time'   => 'sometimes|date',
        ];
    }
}

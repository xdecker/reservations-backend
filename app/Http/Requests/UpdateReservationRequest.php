<?php

namespace App\Http\Requests;
use App\Models\Space;
use Carbon\Carbon;
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

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $space = Space::find($this->space_id ?? $this->reservation->space_id);
            if ($space) {
                $start = Carbon::parse($this->start_time);
                $end   = Carbon::parse($this->end_time);

                if ($start->format('H:i') < $space->available_from || $end->format('H:i') > $space->available_to) {
                    $validator->errors()->add(
                        'time', 
                        'The reservation must be within the available hours of the space (' . $space->available_from . ' - ' . $space->available_to . ').'
                    );
                }

                // Bloques de 30min para calendario limpio
                if ($start->minute % 30 !== 0 || $end->minute % 30 !== 0) {
                    $validator->errors()->add('time', 'Reservation must start and end in 30-minute blocks.');
                }

                // Que dure almenos 30min
                if ($start->diffInMinutes($end) % 30 !== 0) {
                    $validator->errors()->add('time', 'Reservation duration must be a block of 30 minutes.');
                }
            }
        });
    }
}

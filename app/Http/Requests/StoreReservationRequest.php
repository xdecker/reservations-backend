<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Space;
use Carbon\Carbon;
class StoreReservationRequest extends FormRequest
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
            'event_name' => 'required|string|max:255',
            'start_time' => 'required|date|after:now',
            'end_time'   => 'required|date|after:start_time',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $space = Space::find($this->space_id);
            if ($space) {
                $start = Carbon::parse($this->start_time);
                $end   = Carbon::parse($this->end_time);

                if ($start->format('H:i') < $space->available_from || $end->format('H:i') > $space->available_to) {
                    $validator->errors()->add(
                        'time', 
                        'La reserva debe estar dentro del horario disponible del espacio (' . $space->available_from . ' - ' . $space->available_to . ').'
                    );
                }

                // Bloques de 30min para calendario limpio
                if ($start->minute % 30 !== 0 || $end->minute % 30 !== 0) {
                    $validator->errors()->add('time', 'La reserva debe iniciar y finalizar en periodos de 30 minutos.');
                }

                // Que dure almenos 30min
                if ($start->diffInMinutes($end) % 30 !== 0) {
                    $validator->errors()->add('time', 'La duración de la reserva debe ser en bloques de 30 minutos.');
                }
            }
        });
    }
}

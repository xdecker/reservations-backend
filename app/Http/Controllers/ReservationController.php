<?php

namespace App\Http\Controllers;
use App\Http\Requests\StoreReservationRequest;
use App\Http\Requests\UpdateReservationRequest;
use App\Models\Reservation;
use App\Models\Space;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    public function getAll(){
        $reservations = Reservation::where('user_id', auth()->id())
        ->where('active', true)
        ->with('space')
        ->orderBy('start_time')
        ->get();
        return response()->json([
            'data' => $reservations,
            'message' => 'Reservations retrieved successfully'
        ]);
    }

    public function get($id){
        $reservation = Reservation::where('id', $id)
        ->where('active', true)
        ->where('user_id', auth()->id())
        ->first();

        if (! $reservation) {
            return response()->json([
                'message' => 'The reservation does not exist or you do not have access'
            ], 404);
        }
        return response()->json([
            'data' => $reservation,
            'message' => 'Reservation retrieved successfully'
        ]);
    }

    public function create(StoreReservationRequest $request)
    {
        $data = $request->validated();

        if(! Space::where('id', $data['space_id'])->where('active', true)->exists()){
            return response()->json([
                'message' => 'The selected space does not exist or is no longer available'
            ], 404);
        }

        $exists = Reservation::where('space_id', $data['space_id'])
            ->where('active', true)
            ->where(function ($q) use ($data) {
                $q->where('start_time', '<', $data['end_time'])
                  ->where('end_time', '>', $data['start_time']);
            })
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'The space is already reserved at that time'
            ], 422);
        }

        $reservation = Reservation::create([
            'user_id'    => auth()->id(),
            'space_id'   => $data['space_id'],
            'event_name' => $data['event_name'],
            'start_time' => $data['start_time'],
            'end_time'   => $data['end_time'],
        ]);

        return response()->json([
            'data' => $reservation,
            'message' => 'Reservation created successfully'
        ], 201);
    }

    public function update(UpdateReservationRequest $request, $id){
        $reservation = Reservation::where('id', $id)
        ->where('active', true)
        ->where('user_id', auth()->id())
        ->first();

        if (! $reservation) {
            return response()->json([
                'message' => 'The reservation does not exist or you do not have access'
            ], 404);
        }

        $data = $request->validated();
        $start = $data['start_time'] ?? $reservation->start_time;
        $end   = $data['end_time'] ?? $reservation->end_time;

        if ($end <= $start) {
            return response()->json([
                'message' => 'End time must be greater than start time'
            ], 422);
        }

        //validar overlap pero de distintas reservas
        $overlap = Reservation::where('space_id', $reservation->space_id)
            ->where('active', true)
            ->where('id', '!=', $reservation->id)
            ->where(function ($q) use ($start, $end) {
                $q->where('start_time', '<', $end)
                  ->where('end_time', '>', $start);
            })
            ->exists();

        if ($overlap) {
            return response()->json([
                'message' => 'The space is already reserved at that time'
            ], 422);
        }

        $reservation->update($data);

        return response()->json([
            'data' => $reservation,
            'message' => 'Reservation updated successfully'
        ]);
    }

    public function delete($id){
        $reservation = Reservation::where('id', $id)
        ->where('active', true)
        ->where('user_id', auth()->id())
        ->first();

        if (! $reservation) {
            return response()->json([
                'message' => 'The reservation does not exist or you do not have access'
            ], 404);
        }

        $reservation->update(['active' => false]);

        return response()->json([
            'data' => null,
            'message' => 'Reservation cancelled successfully'
        ]);
    }
}

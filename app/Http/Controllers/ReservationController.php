<?php

namespace App\Http\Controllers;
use App\Http\Requests\StoreReservationRequest;
use App\Http\Requests\UpdateReservationRequest;
use App\Models\Reservation;
use App\Models\Space;
use Illuminate\Http\Request;

/**
 * @OA\Tag(name="Reservations", description="Endpoints for managing reservations")
 */

class ReservationController extends Controller
{
    /**
     * @OA\Get(
     *     path="/reservations",
     *     tags={"Reservations"},
     *     summary="Get all reservations for the authenticated user",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="OK")
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/reservations/{id}",
     *     tags={"Reservations"},
     *     summary="Get a reservation by ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="OK"),
     *     @OA\Response(response=404, description="Not found")
     * )
     */
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

    /**
     * @OA\Post(
     *     path="/reservations",
     *     tags={"Reservations"},
     *     summary="Create a new reservation",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"space_id","event_name","start_time","end_time"},
     *             @OA\Property(property="space_id", type="integer"),
     *             @OA\Property(property="event_name", type="string"),
     *             @OA\Property(property="start_time", type="string", format="date-time"),
     *             @OA\Property(property="end_time", type="string", format="date-time")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Reservation created successfully")
     * )
     */
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

    /**
     * @OA\Put(
     *     path="/reservations/{id}",
     *     tags={"Reservations"},
     *     summary="Update a reservation",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="event_name", type="string"),
     *             @OA\Property(property="start_time", type="string", format="date-time"),
     *             @OA\Property(property="end_time", type="string", format="date-time")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Reservation updated successfully"),
     *     @OA\Response(response=404, description="Not found")
     * )
     */
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

    /**
     * @OA\Delete(
     *     path="/reservations/{id}",
     *     tags={"Reservations"},
     *     summary="Delete a reservation",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Reservation deleted successfully"),
     *     @OA\Response(response=404, description="Not found")
     * )
     */
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

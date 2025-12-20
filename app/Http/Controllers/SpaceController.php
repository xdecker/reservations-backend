<?php

namespace App\Http\Controllers;
use App\Models\Space;
use Illuminate\Http\Request;

/**
 * @OA\Info(title="Reservations API", version="1.0")
 * @OA\Server(url="http://localhost:8000/api")
 * 
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */

class SpaceController extends Controller
{

    /**
     * @OA\Get(
     *     path="/spaces",
     *     tags={"Spaces"},
     *     summary="Get all spaces",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="OK")
     * )
     */
    public function getAll(){
        return response()->json([
            'data' => Space::where('active', true)->get(),
            'message' => 'Espacios consultados correctamente'
        ]);
    }

    /**
     * @OA\Post(
     *     path="/spaces",
     *     tags={"Spaces"},
     *     summary="Create a new space",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","capacity"},
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="capacity", type="integer"),
     *             @OA\Property(property="description", type="string")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Space created successfully")
     * )
     */
    public function create(Request $request){
        $validated = $request->validate([
            'name' => 'required|string',
            'capacity'=>'required|integer|min:1',
            'description'=>'nullable|string',
            'available_from' =>'required|date_format:H:i:s',
            'available_to' => 'required|date_format:H:i:s',
        ]);

        $space = Space::create($validated);
        return response()->json([
            'data' => $space,
            'message' => 'Espacio creado correctamente'
        ], 201);
    }



    /**
     * @OA\Get(
     *     path="/spaces/{id}",
     *     tags={"Spaces"},
     *     summary="Get a single space by ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Space retrieved successfully"),
     *     @OA\Response(response=404, description="Space not found")
     * )
     */
    public function get($id){
        $space = Space::where('id', $id)
        ->where('active', true)
        ->first();

        if (! $space) {
            return response()->json([
                'message' => 'El espacio seleccionado no existe o ya no está disponible'
            ], 404);
        }
        
        return response()->json([
            'data' => $space,
            'message' => 'Espacio consultado correctamente'
        ]);
    }

    /**
     * @OA\Put(
     *     path="/spaces/{id}",
     *     tags={"Spaces"},
     *     summary="Update a space",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="capacity", type="integer"),
     *             @OA\Property(property="description", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Space updated successfully"),
     *     @OA\Response(response=404, description="Space not found")
     * )
     */
    public function update(Request $request, $id)
    {
        $space = Space::where('id', $id)
        ->where('active', true)
        ->first();

        if (! $space) {
            return response()->json([
                'message' => 'El espacio seleccionado no existe o ya no está disponible'
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string',
            'capacity' => 'sometimes|integer|min:1',
            'description' => 'nullable|string',
            'available_from' => 'sometimes|date_format:H:i:s',
            'available_to' => 'sometimes|date_format:H:i:s',
        ]);

        $space->update($validated);

        return response()->json([
            'data' => $space,
            'message' => 'Espacio actualizado correctamente'
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/spaces/{id}",
     *     tags={"Spaces"},
     *     summary="Delete a space (soft delete)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Space deleted successfully"),
     *     @OA\Response(response=404, description="Space not found")
     * )
     */
    public function delete($id){

        $space = Space::where('id', $id)
        ->where('active', true)
        ->first();

        if (! $space) {
            return response()->json([
                'message' => 'El espacio seleccionado no existe o ya no está disponible'
            ], 404);
        }

        $space->update(['active' => false]);

        return response()->json([
            'data' => null,
            'message' => 'Espacio eliminado correctamente'
        ]);
    }

}

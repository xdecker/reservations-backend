<?php

namespace App\Http\Controllers;
use App\Models\Space;
use Illuminate\Http\Request;

class SpaceController extends Controller
{
    public function getAll(){
        return response()->json([
            'data' => Space::where('active', true)->get(),
            'message' => 'Spaces retrieved successfully'
        ]);
    }

    public function create(Request $request){
        $validated = $request->validate([
            'name' => 'required|string',
            'capacity'=>'required|integer|min:1'
        ]);

        $space = Space::create($validated);
        return response()->json([
            'data' => $space,
            'message' => 'Space created successfully'
        ], 201);
    }

    public function get(Space $space){
        if(! $space->active){
            return response()->json([
                'message' => 'El espacio seleccionado no existe o ya no está disponible'
            ], 404);
        }
        return response()->json([
            'data' => $space,
            'message' => 'Space retrieved successfully'
        ]);
    }

    public function update(Request $request, Space $space)
    {
        if (! $space->active) {
            return response()->json([
                'message' => 'El espacio seleccionado no existe o ya no está disponible'
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string',
            'capacity' => 'sometimes|integer|min:1',
            'description' => 'nullable|string'
        ]);

        $space->update($validated);

        return response()->json([
            'data' => $space,
            'message' => 'Space updated successfully'
        ]);
    }

    public function delete(Space $space){

        if (! $space->active) {
            return response()->json([
                'message' => 'El espacio seleccionado no existe o ya no está disponible'
            ], 404);
        }

        $space->update(['active' => false]);

        return response()->json([
            'data' => null,
            'message' => 'Space deleted successfully'
        ]);
    }

}

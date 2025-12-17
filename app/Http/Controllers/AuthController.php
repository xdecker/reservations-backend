<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class AuthController extends Controller
{
    public function register(Request $request){
        $request->validate([
            'name' => 'required|string',
            'email'=> 'required|email|unique:users',
            'password' => 'required|min:6'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        $token = auth()->login($user);

        return response()->json([
            'token' => $token,
            'user' => $user
        ]);
    }

    public function login(Request $request){
        $data = $request->only('email','password');

        if(! $token = auth()->attempt($data)){
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return response()->json([
            'token' => $token,
            'user' => auth()->user()
        ]);
    }

    public function profile(){
        return response()->json(auth()->user());
    }
}

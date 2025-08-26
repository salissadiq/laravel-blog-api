<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request){
        $validated = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8'
        ]);

        if($validated->fails()){
            return response()->json(['errors' => $validated->errors(), 422]);
        }

        try{
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password)
            ]);
            $token = $user->createToken('auth_token')->plainTextToken;
            return response()->json([
                'access_token' => $token,
                'user' => $user
            ], 201);
        }catch(\Exception $exception){
            return response()->json(['error' => $exception->getMessage()], 500);
        }
    }
    public function login(Request $request){
        $validated = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string'
        ]);
        if($validated->fails()){
            ;return response()->json(['errors' => $validated->errors(), 422]);
        }

        $credentials = [
            'email' => $request->email,
            'password' => $request->password
        ];
        try {
            if(!auth()->attempt($credentials)){
                return response()->json(['error' => 'Invalid credentials'], 422);
            }
            $user = User::where('email', $request->email)->firstOrFail();
            $token = $user->createToken('auth_token')->plainTextToken;
            return response()->json([
                'access_token' => $token,
                'user' =>$user
            ], 200);
        } catch (\Exception $exception) {
            return response()->json([
                'error' => $exception->getMessage()
            ], 500);
        }
    }
    public function logout(Request $request){
        $user = $request->user();
        if(!$user){
            return response()->json(['error' => 'User not authenticated'], 401);
        }
        try {
            $user->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully'], 200);
        } catch (\Exception $exception) {
           return response()->json(['error' => 'Something went wrong'], 500);
        }
    }
}

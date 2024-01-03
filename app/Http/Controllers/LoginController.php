<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        try {
            $credentials = $request->only(['name', 'password']);
    
            if (Auth::attempt($credentials)) {
                $token = $request->user()->createToken('token-name')->plainTextToken;
        
                return response()->json(['message' => 'Login successful = ' . $token])
                ->cookie('token', $token, 60 * 24 * 36500, '/', null, false, true);
            
            }
    
            return response()->json(['message' => 'Login error'], 401);
        } catch (\Exception $e) {
            \Log::error($e);
            return response()->json(['response' => $e->getMessage()], 500);
        }
    }
    


    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
    
        return response()->json(['message' => 'Logout successful']);
    }
    
}

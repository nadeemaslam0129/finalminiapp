<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // Register new user
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'balance' => 0,
        ]);

        return response()->json(['message' => 'User registered', 'user' => $user]);
    }

    // Login user
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials'],
            ]);
        }

        // Create Sanctum token (SPA cookie)
        auth()->login($user);

        return response()->json(['message' => 'Login successful', 'user' => $user]);
    }

    // Logout user
    public function logout(Request $request)
    {
        auth()->logout();
        return response()->json(['message' => 'Logged out']);
    }

    // Get current user
    public function user(Request $request)
    {
        return response()->json($request->user());
    }
}

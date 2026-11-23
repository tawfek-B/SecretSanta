<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validated = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('username', $validated['username'])
            ->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid username or password.',
            ], 401);
        }

        if (!$user->is_active) {
            return response()->json([
                'message' => 'Your account has been disabled.',
            ], 403);
        }

        $token = $user->createToken('flutter-app')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,

            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'nickname' => $user->nickname,
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }
}

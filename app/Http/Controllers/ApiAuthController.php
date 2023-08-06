<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;

class apiAuthController extends Controller
{
    public function register(Request $request)
    {

        $request->validate([
            "name" => "required|min:3",
            "email" => "required|email|unique:users",
            "password" => "required|min:8|confirmed"
        ]);

        if (Gate::denies('isAdmin')) {
            return response()->json([
                "message" => "You are Unauthorized",
            ], 403);
        };
        $user = User::create([
            "name" => $request->name,
            "email" => $request->email,
            "password" => Hash::make($request->password),
            "user_photo" => $request->user_photo ?  $request->user_photo : config("info.default_user_photo")
        ]);

        return response()->json([
            "message" => "User register successful",
        ]);
    }

    public function login(Request $request)
    {

        $request->validate([
            "email" => "required|email",
            "password" => "required|min:8"
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                "message" => "Username or password wrong",
            ]);
        }
        $token = Auth::user()->createToken($request->has("device") ? $request->device : 'unknown')->plainTextToken;
        return response()->json([
            "message" => "Login successfully",
            "user" => Auth::user(),
            "token" => $token
        ]);
    }
    public function logout()
    {
        Auth::user()->currentAccessToken()->delete();
        return response()->json([
            "message" => "logout successful"
        ]);
    }

    public function logoutAll()
    {
        foreach (Auth::user()->tokens as $token) {
            $token->delete();
        }
        return response()->json([
            "message" => "logout all devices successful"
        ]);
    }

    public function devices()
    {
        return Auth::user()->tokens;
    }
}

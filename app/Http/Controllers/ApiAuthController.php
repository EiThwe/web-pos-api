<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class apiAuthController extends Controller
{
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

        return Auth::user()->createToken($request->has("device") ? $request->device : 'unknown');
    }
}

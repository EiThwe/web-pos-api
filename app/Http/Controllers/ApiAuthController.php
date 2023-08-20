<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;

class apiAuthController extends Controller
{
    public function register(Request $request)
    {
        if (Gate::denies('isAdmin')) {
            return response()->json([
                "message" => "You are Unauthorized",
            ], 403);
        };

        $request->validate([
            "name" => "required|min:3",
            "phone" => "required|numeric|min:9",
            "date_of_birth" => "required|date",
            "gender" => "required|in:male,female",
            "address" => "required|min:50",
            "email" => "required|email|unique:users",
            "password" => "required|min:8|confirmed",
            "user_photo" => "url",
        ]);


        $user = User::create([
            "name" => $request->name,
            "phone" => $request->phone,
            "date_of_birth" => $request->date_of_birth,
            "gender" => $request->gender,
            "address" => $request->address,
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
            "device" => $request->device ?? "unknown",
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

    public function profile()
    {
        $id = Auth::id();
        $user = User::find($id);
        return response()->json(["user" => $user]);
    }

    public function profileUpdate(UpdateProfileRequest $request)
    {
        $id = Auth::id();
        $user = User::find($id);
        $user->name = $request->name ?? $user->name;
        $user->phone = $request->phone ?? $user->phone;
        $user->date_of_birth = $request->date_of_birth ?? $user->date_of_birth;
        $user->gender = $request->gender ?? $user->gender;
        $user->address = $request->address ?? $user->address;
        $user->email = $request->email ?? $user->email;
        $user->user_photo = $request->user_photo ?? $user->user_photo;
        $user->update();
        return response()->json(["message" => "Profile is updated successfully"]);
    }

    public function changePassword(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Current password is incorrect'], 401);
        }

        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return response()->json(['message' => 'Password updated successfully']);
    }
}

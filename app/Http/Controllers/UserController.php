<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function users()
    {
        if (Gate::denies("isAdmin")) {
            return response()->json([
                "message" => "Unauthorized"
            ]);
        }


        $users = User::whereNot("id", Auth::id())->where("status", "active")->latest("id")->paginate(10)->withQueryString();
        return response()->json(["users" => $users]);
    }

    public function banUsers()
    {
        if (Gate::denies("isAdmin")) {
            return response()->json([
                "message" => "Unauthorized"
            ]);
        }


        $users = User::where("status", "ban")->latest("id")->paginate(10)->withQueryString();
        return response()->json(["users" => $users]);
    }

    public function user($id)
    {
        if (Gate::denies("isAdmin")) {
            return response()->json([
                "message" => "Unauthorized"
            ]);
        }
        $user = User::find($id);
        if (is_null($user)) {
            return response()->json([
                "message" => "User not found"
            ], 404);
        }
        return response()->json(["user" => $user]);
    }

    public function userUpdate(UpdateUserRequest $request, $id)
    {
        if (Gate::denies("isAdmin")) {
            return response()->json([
                "message" => "Unauthorized"
            ]);
        }

        $user = User::find($id);
        $user->name = $request->name ?? $user->name;
        $user->phone = $request->phone ?? $user->phone;
        $user->date_of_birth = $request->date_of_birth ?? $user->date_of_birth;
        $user->gender = $request->gender ?? $user->gender;
        $user->address = $request->address ?? $user->address;
        $user->email = $request->email ?? $user->email;
        // $user->status = $request->status ?? $user->status;
        $user->user_photo = $request->user_photo ?? $user->user_photo;
        $user->password = $request->password ? Hash::make($request->password) : $user->password;
        $user->update();

        return response()->json(["message" => "User info is updated successfully"]);
    }

    public function userBan(UpdateUserRequest $request, $id)
    {
        if (Gate::denies("isAdmin")) {
            return response()->json([
                "message" => "Unauthorized"
            ]);
        }
        $user = User::find($id);
        $user->status = "ban";
        $user->update();

        return response()->json(["message" => "User is banned successfully"]);
    }

    public function userRestore(UpdateUserRequest $request, $id)
    {
        if (Gate::denies("isAdmin")) {
            return response()->json([
                "message" => "Unauthorized"
            ]);
        }
        $user = User::find($id);
        $user->status = "active";
        $user->update();

        return response()->json(["message" => "User is restore successfully"]);
    }

    public function userDelete($id)
    {
        if (Gate::denies("isAdmin")) {
            return response()->json([
                "message" => "Unauthorized"
            ]);
        }
        $user = User::find($id);
        if (is_null($user)) {
            return response()->json([
                "message" => "User not found"
            ], 404);
        }
        $user->delete();
        return response()->json(["message" => "A user is deleted successfully"]);
    }
}

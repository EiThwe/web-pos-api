<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class UserController extends Controller
{
    public function users()
    {
        if (Gate::denies("isAdmin")) {
            return response()->json([
                "message" => "Unauthorized"
            ]);
        }
        $users = User::latest("id")->paginate(10)->withQueryString();
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

<?php

namespace App\Http\Controllers;

use App\Models\Photo;
use App\Http\Requests\StorePhotoRequest;
use App\Http\Requests\UpdatePhotoRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PhotoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $photos = Photo::when(Auth::user()->role !== 'admin', function ($query) {
            $query->where("user_id", Auth::id());
        })->latest("id")->paginate(10)->withQueryString();
        return response()->json(["data" => $photos]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function upload(StorePhotoRequest $request)
    {
        //$savedProfilePic = null;
        // $fileExt = null;
        // $fileName = null;
        if ($request->hasFile('photo')) {
            $fileExt =  $request->file('photo')->extension();
            $fileName = $request->file('photo')->getClientOriginalName();
            $savedPhoto = $request->file("photo")->store("public/media");
        }
        $photoUrl = asset(Storage::url($savedPhoto));

        $photo = Photo::create([
            "url" => $photoUrl,
            "name" => $fileName,
            "ext" => $fileExt,
            "user_id" => Auth::id()
        ]);
        return response()->json(["data" => $photo]);
    }

    /**
     * Display the specified resource.
     */


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $photo  = Photo::find($id);
        $this->authorize("delete", $photo);
        if (is_null($photo)) {
            return response()->json([
                "message" => "photo not found"
            ], 404);
        }
        $photo->delete();
        return response()->json([
            "message" => "A photo is deleted successfully"
        ], 200);
    }
}

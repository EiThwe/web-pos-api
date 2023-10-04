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
        return response()->json($photos);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function upload(StorePhotoRequest $request)
    {

        if ($request->hasFile('photos')) {
            $photos = $request->file("photos");
            $savedPhotos = [];

            foreach ($photos as $photo) {
                $savedPhoto = $photo->store("public/media");
                $fileExt =  $photo->extension();
                $fileName = $photo->getClientOriginalName();
                $fileSizeBytes =  $photo->getSize();
                $fileSizeMB = round($fileSizeBytes / (1024 * 1024), 2);
                $photoUrl = asset(Storage::url($savedPhoto));

                $savedPhotos[] = [
                    "url" => $photoUrl,
                    "name" => $fileName,
                    "ext" => $fileExt,
                    "file_size" => $fileSizeMB,
                    "user_id" => Auth::id(),
                    "created_at" => now(),
                    "updated_at" => now(),
                ];
                # code...
            }

            Photo::insert($savedPhotos);
        }

        return response()->json(["message" => "uploaded successfully"], 201);
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

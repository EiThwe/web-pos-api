<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBrandRequest;
use App\Http\Requests\UpdateBrandRequest;
use App\Http\Resources\BrandResource;
use App\Models\Brand;
// use Illuminate\Auth\Access\Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $brands = Brand::latest("id")->paginate(10)->withQueryString();
        return  BrandResource::collection($brands);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBrandRequest $request)
    {
        $brand = Brand::create([
            "name" => $request->name,
            "company" => $request->company,
            "information" => $request->information,
            "user_id" => Auth::id(),
            "photo" => $request->photo ? $request->photo : config("info.default_photo")
        ]);
        return new BrandResource($brand);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $brand = Brand::find($id);
        if (is_null($brand)) {
            return response()->json([
                "message" => "Brand not found"
            ], 404);
        }
        return new BrandResource($brand);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBrandRequest $request, string $id)
    {
        $brand = Brand::find($id);
        if (is_null($brand)) {
            return response()->json([
                "message" => "Brand not found"
            ], 404);
        }
        if ($request->has('name')) {
            $brand->name = $request->name;
        }

        if ($request->has('company')) {
            $brand->company = $request->company;
        }

        if ($request->has('information')) {
            $brand->information = $request->information;
        }
        if ($request->has('photo')) {
            $brand->photo = $request->photo;
        }
        $brand->update();

        return new BrandResource($brand);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        if (Gate::denies("isAdmin")) {
            return response()->json([
                "message" => "Unauthorized"
            ]);
        }
        $brand = Brand::find($id);
        if (is_null($brand)) {
            return response()->json([
                "message" => "Brand not found"
            ], 404);
        }
        $brand->delete();
        return response()->json([
            "message" => "A brand is deleted successfully"
        ], 200);
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBrandRequest;
use App\Http\Requests\UpdateBrandRequest;
use App\Http\Resources\BrandResource;
use App\Models\Brand;
use Illuminate\Database\Eloquent\Builder;
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
        $brands = Brand::when(request()->has("search"), function ($query) {
            $query->where(function (Builder $builder) {
                $search = request()->search;

                $builder->where("name", "like", "%" . $search . "%");
                $builder->orWhere("company", "like", "%" . $search . "%");
                $builder->orWhere("agent", "like", "%" . $search . "%");
                $builder->orWhere("phone", "like", "%" . $search . "%");
                $builder->orWhere("information", "like", "%" . $search . "%");
                // $builder->orWhere("total_stock", "like", "%" . $search . "%");
            });
        })->when(request()->has('orderBy'), function ($query) {
            $sortType = request()->sort ?? 'asc';
            $query->orderBy(request()->orderBy, $sortType);
        })->latest("id")->paginate(10)->withQueryString();
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
            "photo" => $request->photo ? $request->photo : config("info.default_photo"),
            "agent" => $request->agent,
            "phone" => $request->phone
        ]);
        return response()->json(['message' => "brand has been created successfully"], 201);
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
        $brand->name = $request->name ?? $brand->name;
        $brand->company = $request->company ?? $brand->company;
        $brand->information = $request->information ?? $brand->information;
        $brand->photo = $request->photo ?? $brand->photo;
        $brand->phone = $request->phone ?? $brand->phone;
        $brand->agent = $request->agent ?? $brand->agent;
        $brand->update();

        return  response()->json(['message' => "brand has been updated successfully"]);
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

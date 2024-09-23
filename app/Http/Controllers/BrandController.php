<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Traits\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    use Response;
    public function index(): JsonResponse
    {
        $brands = Brand::paginate(10);
        return response()->json([
            'success' => true,
            'message' => 'brands successfully fetched',
            'brands' => $brands
        ], 201);
    }

    public function show(string|int $id)
    {
        $brand = Brand::find($id);
        if(!$brand){
            return 
        }
    }
}

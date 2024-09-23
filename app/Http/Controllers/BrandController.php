<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Traits\Response;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BrandController extends Controller
{
    use Response;

    // TODO: solve paginate
    public function index(Request $request): JsonResponse
    {
        try {
            $columns = ['id', 'name'];

            $paginate = (int) $request->query('paginate', 10);
            $page = (int) $request->query('page', 1);

            $query = Brand::select($columns);

            $brands = $paginate > 0 ?
                $query->paginate($paginate, ['*'], 'page', $page) :
                $query->get();

            if ($brands->isEmpty()) {
                return $this->sendResponse('No brands available', false, [], 404);
            }

            $returnedBrands = $brands->map(function ($brand) {
                return [
                    'id' => $brand->id,
                    'name' => $brand->name,
                ];
            });

            return $this->sendResponse("Brands successfully fetched", true, $returnedBrands, 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while login. Please try again later.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(string|int $id): JsonResponse
    {
        try {
            $brand = Brand::find($id);
            if (!$brand) {
                return $this->sendResponse("No brand found with the provided ID", false, [], 404);
            }

            return $this->sendResponse("Brand successfully fetched", true, $brand, 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while login. Please try again later.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $rules = [
            'name' => 'required|string|min:3|max:50|unique:brands,name',
        ];

        $messages = [
            'name.required' => 'The name is required. Please provide it.',
            'name.string' => 'The name must be a string.',
            'name.min' => 'The name must be at least 3 characters long.',
            'name.max' => 'The name must not exceed 50 characters.',
            'name.unique' => 'The name has already been taken. Please choose another.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $brand = new Brand();
            $brand->name = $request->name;
            $brand->save();

            $returnedBrand = [
                'id' => $brand->id,
                'name' => $brand->name,
            ];

            return $this->sendResponse('Brand created successfully!', true, $returnedBrand, 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while login. Please try again later.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // TODO: unique dose not work
    public function update(Request $request, string|int $id): JsonResponse
    {
        $method = $request->method();

        $rules = [
            'name' => $method === 'PATCH'
                ? 'sometimes|string|min:3|max:50|unique:brands,name,{$id}'
                : 'required|string|min:3|max:50|unique:brands,name,{$id}',
        ];

        $messages = [
            'name.required' => 'The name is required. Please provide it.',
            'name.string' => 'The name must be a string.',
            'name.min' => 'The name must be at least 3 characters long.',
            'name.max' => 'The name must not exceed 50 characters.',
            'name.unique' => 'The name has already been taken. Please choose another.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $brand = Brand::find($id);
            if (!$brand) {
                return $this->sendResponse("No brand found with the provided ID", false, [], 404);
            }

            $method === 'PATCH' ? $brand->fill($request->only('name')) : $brand->name = $request->name;

            $brand->save();

            return $this->sendResponse('Brand updated successfully!', true,  $brand, 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while login. Please try again later.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function delete(Request $request, string|int $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $brand = Brand::find($id);
            if (!$brand) {
                return $this->sendResponse("No brand found with the provided ID", false, [], 404);
            }
            $brand->delete();
            DB::commit();
            return $this->sendResponse('Brand deleted successfully!', true, [], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while login. Please try again later.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

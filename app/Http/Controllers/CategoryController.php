<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Traits\Image;
use App\Traits\Response;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    use Image, Response;
    // TODO: solve paginate
    public function index(Request $request): JsonResponse
    {
        try {
            $columns = ['id', 'name', 'image'];

            $paginate = (int) $request->query('paginate', 10);
            $page = (int) $request->query('page', 1);

            $query = Category::select($columns);

            $categories = $paginate > 0 ?
                $query->paginate($paginate, ['*'], 'page', $page) :
                $query->get();

            if ($categories->isEmpty()) {
                return $this->sendResponse('No categories available', false, [], 404);
            }

            $returnedCategories = $categories->map(function ($category): array {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'image' => $category->image,
                ];
            });

            return $this->sendResponse("Categories successfully fetched", true, $returnedCategories, 200);
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
            $category = Category::find($id);
            if (!$category) {
                return $this->sendResponse("No category found with the provided ID", false, [], 404);
            }

            return $this->sendResponse("Category successfully fetched", true, $category, 200);
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
            'name' => 'required|string|min:3|max:50|unique:categories,name',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];

        $messages = [
            'name.required' => 'The name is required. Please provide it.',
            'name.string' => 'The name must be a string.',
            'name.min' => 'The name must be at least 3 characters long.',
            'name.max' => 'The name must not exceed 50 characters.',
            'name.unique' => 'The name has already been taken. Please choose another.',
            'image.required' => 'An image is required. Please upload one.',
            'image.image' => 'The file must be an image.',
            'image.mimes' => 'The image must be of type: jpeg, png, jpg, gif.',
            'image.max' => 'The image must not exceed 2MB.',
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
            $category = new Category();

            if ($request->hasFile('image')) {
                $imagePath = $this->uploadImage($request->file('image'), 'assets/uploads/categories');
                $category->image = $imagePath;
            } else {
                return $this->sendResponse("Please provide an image file", false, [], 304);
            }

            $category->name = $request->name;
            $category->save();

            $returnedCategory = [
                'id' => $category->id,
                'name' => $category->name,
                'image' => $category->image,
            ];

            return $this->sendResponse('Category created successfully!', true, $returnedCategory, 201);
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
                ? 'sometimes|string|min:3|max:50|unique:categories,name,{$id}'
                : 'required|string|min:3|max:50|unique:categories,name,{$id}',
            'image' => $method === 'PATCH'
                ? 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048'
                : 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];

        $messages = [
            'name.required' => 'The name is required. Please provide it.',
            'name.string' => 'The name must be a string.',
            'name.min' => 'The name must be at least 3 characters long.',
            'name.max' => 'The name must not exceed 50 characters.',
            'name.unique' => 'The name has already been taken. Please choose another.',
            'image.required' => 'An image is required. Please upload one.',
            'image.sometimes' => 'An image file is optional, but if provided, it must be valid.',
            'image.image' => 'The file must be an image.',
            'image.mimes' => 'The image must be of type: jpeg, png, jpg, gif.',
            'image.max' => 'The image must not exceed 2MB.',
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
            $category = Category::find($id);
            if (!$category) {
                return $this->sendResponse("No category found with the provided ID", false, [], 404);
            }

            if ($method === 'PATCH') {
                $category->fill($request->only('name'));

                // Update image only if it's present in the request
                if ($request->hasFile('image')) {
                    $imagePath = $this->updateImage($request, 'image', 'assets/uploads/categories', $category->image);
                    $category->image = $imagePath;
                }
            } else {
                $category->name = $request->name;

                if ($request->hasFile('image')) {
                    $imagePath = $this->uploadImage($request->file('image'), 'assets/uploads/categories');
                    $category->image = $imagePath;
                }
            }

            $category->save();

            return $this->sendResponse('Category updated successfully!', true,  $category, 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while login. Please try again later.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function delete(string|int $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $category = Category::find($id);
            if (!$category) {
                return $this->sendResponse("No category found with the provided ID", false, [], 404);
            }

            $this->deleteImage($category->image);
            $category->delete();
            DB::commit();
            return $this->sendResponse('Category deleted successfully!', true, [], 200);
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

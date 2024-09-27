<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Repositories\Category\CategoryRepositoryInterface;
use App\Traits\Image;
use App\Traits\Response;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    use Image, Response;

    private $categoryRepository;

    public function __construct(CategoryRepositoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }


    public function index(Request $request): JsonResponse
    {
        try {
            $categories = $this->categoryRepository->getAllCategories($request);

            if ($categories->isEmpty())  return $this->sendResponse('No categories available', false, [], 404);

            $returnedCategories = CategoryResource::collection($categories);

            return $this->sendResponse("Categories successfully fetched", true, $returnedCategories, 200);
        } catch (Exception $e) {
            return $this->errorResponse('An error occurred while fetching categories. Please try again later.', $e->getMessage());
        }
    }

    public function show(string|int $id): JsonResponse
    {
        try {
            $category = $this->categoryRepository->findCategoryById($id);

            if (!$category)  return $this->sendResponse("No category found with the provided ID", false, [], 404);

            $returnedCategory = CategoryResource::make($category);

            return $this->sendResponse("Category successfully fetched", true, $returnedCategory, 200);
        } catch (Exception $e) {
            return $this->errorResponse('An error occurred while fetching category. Please try again later.', $e->getMessage());
        }
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        try {
            $data = $request->only('name');

            if ($request->hasFile('image')) {
                $data['image'] = $this->uploadImage($request->file('image'), 'assets/uploads/categories');
            } else {
                return $this->sendResponse("Please provide an image file", false, [], 304);
            }

            $category = $this->categoryRepository->createCategory($data);

            $returnedCategory = CategoryResource::make($category);

            return $this->sendResponse('Category created successfully!', true, $returnedCategory, 201);
        } catch (Exception $e) {
            return $this->errorResponse('An error occurred while login. Please try again later.',  $e->getMessage());
        }
    }

    public function update(Request $request, string|int $id): JsonResponse
    {
        try {
            $category = $this->categoryRepository->updateCategory($request, $id);

            if (!$category) return $this->sendResponse("No category found with the provided ID", false, [], 404);

            $returnedCategory = CategoryResource::make($category);

            return $this->sendResponse('Category updated successfully!', true,  $returnedCategory, 201);
        } catch (Exception $e) {
            return $this->errorResponse('An error occurred while login. Please try again later.', $e->getMessage());
        }
    }

    public function delete(string|int $id): JsonResponse
    {

        try {
            $category = $this->categoryRepository->deleteCategory($id);

            if (!$category) return $this->sendResponse("No category found with the provided ID", false, [], 404);

            return $this->sendResponse('Category deleted successfully!', true, [], 200);
        } catch (Exception $e) {
            return $this->errorResponse('An error occurred while login. Please try again later.', $e->getMessage());
        }
    }
}

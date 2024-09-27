<?php

namespace App\Repositories\Category;

use App\Models\Category;
use App\Traits\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CategoryRepository implements CategoryRepositoryInterface
{
    use Image;
    public function getAllCategories(Request $request)
    {

        $columns = ['id', 'name', 'image'];

        $paginate = (int) $request->query('paginate', 10);
        $page = (int) $request->query('page', 1);

        $cacheKey = 'categories_page_' . $page . '_paginate_' . $paginate;

        $categories = Cache::remember($cacheKey, now()->addHour(), function () use ($columns, $paginate, $page) {
            $query = Category::select($columns);

            return $paginate  ?
                $query->paginate($paginate, ['*'], 'page', $page) :
                $query->get();
        });

        return  $categories;
    }
    public function findCategoryById(string|int $id)
    {
        $cacheKey = 'category_' . $id;

        $category = Cache::remember($cacheKey, now()->addHours(2), function () use ($id) {
            return  Category::find($id);
        });
        return $category;
    }
    public function createCategory(array $data): mixed
    {
        return DB::transaction(function () use ($data) {
            $category = new Category();
            $category->name = $data['name'];
            $category->image = $data['image']; // Assuming image is provided in $data
            $category->save();

            Cache::forget('categories_page_1_paginate_10');

            return $category;
        });
    }
    public function updateCategory(Request $request, string|int $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $category = Category::find($id);

            if (!$category) {
                return null;
            }

            if ($request->isMethod('patch')) {
                if ($request->hasFile('image')) {
                    $imagePath = $this->updateImage($request, 'image', 'assets/uploads/categories', $category->image);
                    $category->image = $imagePath;
                }

                if ($request->has('name')) {
                    $category->fill($request->only('name'));
                }
            } else {
                $category->name = $request->name;

                if ($request->hasFile('image')) {
                    $imagePath = $this->updateImage($request, 'image', 'assets/uploads/categories', $category->image);
                    $category->image = $imagePath;
                }
            }

            $category->save();

            // Clear the cache for the updated category
            Cache::forget('category_' . $id);

            return $category;
        });
    }
    public function deleteCategory(string|int $id)
    {
        return DB::transaction(function () use ($id) {
            $category = Category::find($id);

            if ($category) {
                $this->deleteImage($category->image);
                $category->delete();

                // Clear cache for deleted brand
                Cache::forget('brand_' . $id);
            }

            return $category;
        });
    }
}

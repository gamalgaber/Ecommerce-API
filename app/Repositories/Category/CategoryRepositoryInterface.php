<?php

namespace App\Repositories\Category;

use Illuminate\Http\Request;

interface CategoryRepositoryInterface
{
    public function getAllCategories(Request $request);
    public function findCategoryById(string|int $id);
    public function createCategory(array $data);
    public function updateCategory(Request $request, string|int $id);
    public function deleteCategory(string|int $id);
}

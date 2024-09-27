<?php

namespace App\Repositories\Brand;

use Illuminate\Http\Request;

interface BrandRepositoryInterface
{
    public function getAllBrands(Request $request);
    public function findBrandById(string|int $id);
    public function createBrand(array $data);
    public function updateBrand(string|int $id, array $data);
    public function deleteBrand(string|int $id);
}

<?php

namespace App\Repositories\Brand;

use App\Models\Brand;
use App\Repositories\Brand\BrandRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class BrandRepository implements BrandRepositoryInterface
{
    public function getAllBrands(Request $request)
    {
        $columns = ['id', 'name'];

        $paginate = (int) $request->query('paginate', 10);
        $page = (int) $request->query('page', 1);

        $cacheKey = 'brands_page_' . $page . '_paginate_' . $paginate;

        $brands =  Cache::remember($cacheKey, now()->addHour(), function () use ($columns, $paginate, $page) {
            $query = Brand::select($columns);

            return $paginate ?
                $query->paginate($paginate, ['*'], 'page', $page) :
                $query->get();
        });

        return $brands;
    }

    public function findBrandById(string|int $id)
    {
        $cacheKey = 'brand_' . $id;
        $brand = Cache::remember($cacheKey, now()->addHour(), function () use ($id) {
            return Brand::find($id);
        });

        return $brand;
    }

    public function createBrand(array $data)
    {
        return DB::transaction(function () use ($data) {
            $brand = new Brand();
            $brand->name = $data['name'];
            $brand->save();

            // Clear cache for first page (assuming that's where new brands are listed)
            Cache::forget('brands_page_1_paginate_10');

            return $brand;
        });
    }

    public function updateBrand(string|int $id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $brand = Brand::find($id);

            if ($brand) {
                $brand->update($data);

                // Clear cache for the updated brand
                Cache::forget('brand_' . $id);
            }

            return $brand;
        });
    }

    public function deleteBrand(string|int $id)
    {
        return DB::transaction(function () use ($id) {
            $brand = Brand::find($id);

            if ($brand) {
                $brand->delete();

                // Clear cache for deleted brand
                Cache::forget('brand_' . $id);
            }

            return $brand;
        });
    }
}

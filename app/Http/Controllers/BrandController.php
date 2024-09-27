<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBrandRequest;
use App\Http\Requests\UpdateBrandRequest;
use App\Http\Resources\BrandResource;
use App\Models\Brand;
use App\Repositories\Brand\BrandRepositoryInterface;
use App\Traits\Response;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BrandController extends Controller
{
    use Response;

    private $brandRepository;

    public function __construct(BrandRepositoryInterface $brandRepository)
    {
        $this->brandRepository = $brandRepository;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $brands = $this->brandRepository->getAllBrands($request);

            if ($brands->isEmpty()) return $this->sendResponse('No brands available', false, [], 404);

            $returnedBrands = BrandResource::collection($brands);

            return $this->sendResponse("Brands successfully fetched", true, $returnedBrands, 200);
        } catch (Exception $e) {
            return $this->errorResponse('An error occurred while fetching brands. Please try again later.', $e->getMessage());
        }
    }

    public function show(string|int $id): JsonResponse
    {
        try {
            $brand = $this->brandRepository->findBrandById($id);

            if (!$brand) return $this->sendResponse("No brand found with the provided ID", false, [], 404);

            $returnedBrand = BrandResource::make($brand);

            return $this->sendResponse("Brand successfully fetched", true, $returnedBrand, 200);
        } catch (Exception $e) {
            return $this->errorResponse('An error occurred while fetching brand. Please try again later.', $e->getMessage());
        }
    }

    public function store(StoreBrandRequest $request): JsonResponse
    {
        try {
            $brand = $this->brandRepository->createBrand($request->only('name'));

            $returnedBrand = BrandResource::make($brand);

            return $this->sendResponse('Brand created successfully!', true, $returnedBrand, 201);
        } catch (Exception $e) {
            return $this->errorResponse('An error occurred while creating brand. Please try again later.', $e->getMessage());
        }
    }

    public function update(UpdateBrandRequest $request, string|int $id): JsonResponse
    {

        try {
            $method = $request->method();
            $data = $method === 'PATCH' ? $request->only('name') : ['name' => $request->name];

            $brand = $this->brandRepository->updateBrand($id, $data);

            if (!$brand)  return $this->sendResponse("No brand found with the provided ID", false, [], 404);

            $returnedBrand = BrandResource::make($brand);

            return $this->sendResponse('Brand updated successfully!', true,  $returnedBrand, 201);
        } catch (Exception $e) {
            return $this->errorResponse('An error occurred while updating brand. Please try again later.', $e->getMessage());
        }
    }

    public function delete(string|int $id): JsonResponse
    {

        try {
            $brand = $this->brandRepository->deleteBrand($id);

            if (!$brand)  return $this->sendResponse("No brand found with the provided ID", false, [], 404);

            return $this->sendResponse('Brand deleted successfully!', true, [], 200);
        } catch (Exception $e) {
            return $this->errorResponse('An error occurred while deleting brand. Please try again later.', $e->getMessage());
        }
    }


    // public function index_example(Request $request): JsonResponse
    // {
    //     try {
    //         // $columns = ['id', 'name'];

    //         // $paginate = (int) $request->input('paginate', 10);
    //         // $page = (int) $request->input('page', 1);

    //         $cachedBrand = Redis::get('brands_');

    //         if (isset($cachedBrand)) {
    //             $brands = json_decode($cachedBrand, false);
    //             return $this->sendResponse("Brands Fetched from redis", true, $brands, 200);
    //         } else {
    //             $brand = Brand::get();
    //             Redis::set('brands_', $brand);

    //             return $this->sendResponse("Brands Fetched from database", true, $brand, 200);
    //         }

    //         // $cacheKey = 'brands_page_' . $page . '_paginate_' . $paginate;
    //         // $brands = Cache::remember($cacheKey, now()->addHour(), function () use ($columns, $paginate, $page) {
    //         //     $query = Brand::select($columns);

    //         //     return $paginate ?
    //         //         $query->paginate($paginate, ['*'], 'page', $page) :
    //         //         $query->get();
    //         // });

    //         // if ($brands->isEmpty()) {
    //         //     return $this->sendResponse('No brands available', false, [], 404);
    //         // }
    //         // return $this->sendResponse("Brands successfully fetched", true, $brands, 200);
    //     } catch (Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'An error occurred while fetching brands. Please try again later.',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }
}

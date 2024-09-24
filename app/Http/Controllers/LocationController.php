<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LocationController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $rules = [
            'area' => 'required|string|min:3|max:80',
            'street' => 'required|string|min:3|max:150',
            'building' => 'required|string|min:3|max:50',
        ];

        $messages = [
            'area.required' => 'The area is required. Please provide it.',
            'area.string' => 'The area must be a string.',
            'area.min' => 'The area must be at least 3 characters long.',
            'area.max' => 'The area must not exceed 80 characters.',

            'street.required' => 'The street is required. Please provide it.',
            'street.string' => 'The street must be a string.',
            'street.min' => 'The street must be at least 3 characters long.',
            'street.max' => 'The street must not exceed 150 characters.',

            'building.required' => 'The building is required. Please provide it.',
            'building.string' => 'The building must be a string.',
            'building.min' => 'The building must be at least 3 characters long.',
            'building.max' => 'The building must not exceed 50 characters.',

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
            DB::beginTransaction();

            $location = new Location();
            $location->user_id = auth()->user()->id;
            $location->area = $request->area;
            $location->street = $request->street;
            $location->building = $request->building;
            $location->save();


            $returnedLocation = [
                'id' => $location->id,
                'user_id' => $location->user_id,
                'street' => $location->user_id,
                'building' => $location->building,
                'area' => $location->area,
            ];

            DB::commit();
            return $this->sendResponse('Location created successfully!', true, $returnedLocation, 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating location. Please try again later.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, string|int $id): JsonResponse
    {
        $method = $request->method();

        $rules = [
            'area' => $method === 'PATCH' ? 'sometimes|string|min:3|max:80' : 'required|string|min:3|max:80',
            'street' => $method === 'PATCH' ? 'sometimes|string|min:3|max:150' : 'required|string|min:3|max:150',
            'building' => $method === 'PATCH' ? 'sometimes|string|min:3|max:50' : 'required|string|min:3|max:50',
        ];

        $messages = [
            'area.required' => 'The area is required. Please provide it.',
            'area.string' => 'The area must be a string.',
            'area.min' => 'The area must be at least 3 characters long.',
            'area.max' => 'The area must not exceed 80 characters.',

            'street.required' => 'The street is required. Please provide it.',
            'street.string' => 'The street must be a string.',
            'street.min' => 'The street must be at least 3 characters long.',
            'street.max' => 'The street must not exceed 150 characters.',

            'building.required' => 'The building is required. Please provide it.',
            'building.string' => 'The building must be a string.',
            'building.min' => 'The building must be at least 3 characters long.',
            'building.max' => 'The building must not exceed 50 characters.',

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
            DB::beginTransaction();
            $location = Location::find($id);
            if (!$location) {
                DB::rollBack();
                return $this->sendResponse("No location found with the provided ID", false, [], 404);
            }

            if ($method === 'PATCH') {
                $location->fill($request->only('area', 'building', 'street'));
            } else {
                $location->area = $request->area;
                $location->street = $request->street;
                $location->building = $request->building;
            }

            $location->save();

            DB::commit();

            return $this->sendResponse('Location updated successfully!', true,  $location, 201);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while login. Please try again later.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function delete(string|int $id): JsonResponse
    {

        try {
            DB::beginTransaction();
            $location = Location::find($id);
            if (!$location) {
                DB::rollBack();
                return $this->sendResponse("No location found with the provided ID", false, [], 404);
            }
            $location->delete();
            DB::commit();
            return $this->sendResponse('Location deleted successfully!', true, [], 200);
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

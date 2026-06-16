<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use illuminate\Http\JsonResponse;
use App\Models\ItemModel;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Requests\UpdateItemRequest;
use Tymon\JWTAuth\Exceptions\JWTException;


class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function itemList(Request $request)
    {
        $items = ItemModel::all();

        return response()->json([
            'status' => 'success',
            'data' => $items
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function addItem(Request $request)
    {
            $validator = Validator::make(request()->all(), [
                'name' => 'required|string|max:50',
                'code' => 'required|string',
                'price' => 'required|numeric',
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
    
        $item = ItemModel::create($validator->validated());
    
            return response()->json([
                'status' => 'success',
                'message' => 'Item successfully created',
                'data' => $item
            ], 201);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        
    }

    /**
     * Display the specified resource.
     */
    public function showItem(String $id)
    {
        try{
            $item = ItemModel::findOrFail($id);
            return response()->json([
                'status' => 'success',
                'data' => $item
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve item: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function editItem(ItemModel $itemModel)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateItem(UpdateItemRequest $request, $id  )
    {
      $validated = $request->validated();

        try {
            $item = ItemModel::findOrFail($id);
            $item->update($validated);

            return response()->json([
                'status' => 'success',
                'message' => 'Item successfully updated',
                'data' => $item
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update item: ' . $e->getMessage()
            ], 500);
        } catch (JWTException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update item, token invalid: ' . $e->getMessage()
            ], 500);
        }  
    }
    /**
     * Remove the specified resource from storage.
     */
    public function deleteItem( $id)
    {
        try {
            $item = ItemModel::findOrFail($id);
            $item->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Item successfully deleted'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete item: ' . $e->getMessage()
            ], 500);
        } catch (JWTException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete item, token invalid: ' . $e->getMessage()
            ], 500);
        }
    }

    protected function respondWithToken($token)
    {
        return [
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => JWTAuth::factory()->getTTL() * 60, // dalam detik
        ];
    }
}

<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\PurchaseShopItemRequest;
use App\Http\Requests\Shop\StoreShopItemRequest;
use App\Http\Requests\Shop\UpdateShopItemRequest;
use App\Models\Shop\ShopItem;
use App\Services\Shop\ShopService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function __construct(
        private readonly ShopService $shopService
    ) {
    }

    /**
     * Display a listing of shop items.
     */
    public function index(): JsonResponse
    {
        $shopItems = $this->shopService->getAllItems();

        return response()->json([
            'shop_items' => $shopItems,
        ]);
    }

    /**
     * Store a newly created shop item.
     */
    public function store(StoreShopItemRequest $request): JsonResponse
    {
        $this->shopService->createItem($request->validated(), $request->user()->id);

        return response()->json([
            'message' => 'Shop item created successfully',
        ], 201);
    }

    /**
     * Display the specified shop item.
     */
    public function show(ShopItem $shopItem): JsonResponse
    {
        return response()->json([
            'shop_item' => $shopItem,
        ]);
    }

    /**
     * Update the specified shop item.
     */
    public function update(UpdateShopItemRequest $request, ShopItem $shopItem): JsonResponse
    {
        $this->shopService->updateItem($shopItem, $request->validated());

        return response()->json([
            'message' => 'Shop item updated successfully',
            'shop_item' => $shopItem->fresh(),
        ]);
    }

    /**
     * Remove the specified shop item.
     */
    public function destroy(Request $request, ShopItem $shopItem): JsonResponse
    {
        $user = $request->user();
        $user->load('role');

        // Only parents can delete shop items
        if (!$user->isParent()) {
            return response()->json([
                'message' => 'Only parents can delete shop items',
            ], 403);
        }

        $this->shopService->deleteItem($shopItem);

        return response()->json([
            'message' => 'Shop item deleted successfully',
        ]);
    }

    /**
     * Purchase a shop item.
     */
    public function purchase(PurchaseShopItemRequest $request, ShopItem $shopItem): JsonResponse
    {
        try {
            $user = $request->user();
            $result = $this->shopService->purchaseItem($user, $shopItem, $request->validated()['quantity']);

            return response()->json([
                'message' => 'Purchase successful',
                'purchase' => $result['purchase'],
                'new_balance' => $user->fresh()->points,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
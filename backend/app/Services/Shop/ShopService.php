<?php

declare(strict_types=1);

namespace App\Services\Shop;

use App\Models\Shop\ShopItem;
use App\Models\Users\User;
use App\Repositories\Shop\ShopItemRepository;
use App\Services\Points\PointService;
use Illuminate\Database\Eloquent\Collection;

class ShopService
{
    public function __construct(
        private ShopItemRepository $shopItemRepository,
        private PointService $pointService
    ) {
    }

    /**
     * Get all shop items.
     */
    public function getAllItems(): Collection
    {
        return $this->shopItemRepository->getAllWithCreator();
    }

    /**
     * Create a new shop item.
     */
    public function createItem(array $data, int $createdBy): ShopItem
    {
        $data['created_by'] = $createdBy;
        return $this->shopItemRepository->store($data);
    }

    /**
     * Update a shop item.
     */
    public function updateItem(ShopItem $shopItem, array $data): bool
    {
        return $this->shopItemRepository->update($shopItem, $data);
    }

    /**
     * Delete a shop item.
     */
    public function deleteItem(ShopItem $shopItem): ?bool
    {
        return $this->shopItemRepository->destroy($shopItem);
    }

    /**
     * Purchase a shop item.
     *
     * @throws \Exception
     */
    public function purchaseItem(User $user, ShopItem $shopItem, int $quantity = 1): array
    {
        return $this->pointService->purchaseShopItem($user, $shopItem, $quantity);
    }
}
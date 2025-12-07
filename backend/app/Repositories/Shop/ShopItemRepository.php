<?php

declare(strict_types=1);

namespace App\Repositories\Shop;

use App\Models\Shop\ShopItem;
use App\Repositories\Repository;
use Illuminate\Database\Eloquent\Collection;

class ShopItemRepository extends Repository
{
    public function __construct()
    {
        $this->model = new ShopItem();
    }

    /**
     * Get all shop items with creator information.
     */
    public function getAllWithCreator(): Collection
    {
        $this->initializeQuery();

        return $this->query
            ->with('creator:id,name')
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
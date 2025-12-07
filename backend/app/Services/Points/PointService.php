<?php

declare(strict_types=1);

namespace App\Services\Points;

use App\Models\Points\PointTransaction;
use App\Models\Shop\Purchase;
use App\Models\Shop\ShopItem;
use App\Models\Tasks\Task;
use App\Models\Users\User;
use Exception;
use Illuminate\Support\Facades\DB;

class PointService
{
    /**
     * Add points to a user for completing a task.
     *
     * @param User $user
     * @param Task $task
     * @return PointTransaction
     */
    public function addPointsForTaskCompletion(User $user, Task $task): PointTransaction
    {
        return DB::transaction(function () use ($user, $task) {
            $previousBalance = $user->points;
            $amount = $task->price;
            $newBalance = $previousBalance + $amount;

            $user->update(['points' => $newBalance]);

            return PointTransaction::create([
                'user_id' => $user->id,
                'task_id' => $task->id,
                'amount' => $amount,
                'previous_balance' => $previousBalance,
                'new_balance' => $newBalance,
                'reason' => 'task_completed',
            ]);
        });
    }

    /**
     * Subtract points from a user for cancelling a task.
     * Points cannot go below 0.
     *
     * @param User $user
     * @param Task $task
     * @return PointTransaction
     */
    public function subtractPointsForTaskCancellation(User $user, Task $task): PointTransaction
    {
        return DB::transaction(function () use ($user, $task) {
            $previousBalance = $user->points;
            $amount = -$task->price;
            $newBalance = max(0, $previousBalance + $amount);

            $user->update(['points' => $newBalance]);

            return PointTransaction::create([
                'user_id' => $user->id,
                'task_id' => $task->id,
                'amount' => $amount,
                'previous_balance' => $previousBalance,
                'new_balance' => $newBalance,
                'reason' => 'task_cancelled',
            ]);
        });
    }

    /**
     * Process a shop item purchase for a user.
     * Deducts points and creates a purchase record.
     *
     * @param User $user
     * @param ShopItem $shopItem
     * @param int $quantity
     * @return array{purchase: Purchase, transaction: PointTransaction}
     * @throws Exception
     */
    public function purchaseShopItem(User $user, ShopItem $shopItem, int $quantity = 1): array
    {
        return DB::transaction(function () use ($user, $shopItem, $quantity) {
            $totalPrice = $shopItem->price * $quantity;

            $this->validatePurchaseRequest($user, $shopItem, $totalPrice, $quantity);

            $pointsData = $this->deductUserPoints($user, $totalPrice);

            $shopItem->decrement('quantity', $quantity);

            return $this->createPurchaseRecords($user, $shopItem, $totalPrice, $quantity, $pointsData);
        });
    }

    /**
     * Validate if purchase request can be fulfilled.
     *
     * @param User $user
     * @param ShopItem $shopItem
     * @param float $totalPrice
     * @param int $quantity
     * @return void
     * @throws Exception
     */
    private function validatePurchaseRequest(User $user, ShopItem $shopItem, float $totalPrice, int $quantity): void
    {
        if ($user->points < $totalPrice) {
            throw new Exception('Insufficient points');
        }

        if ($shopItem->quantity < $quantity) {
            throw new Exception('Insufficient shop item quantity');
        }
    }

    /**
     * Deduct points from user and return balance information.
     *
     * @param User $user
     * @param float $totalPrice
     * @return array{previousBalance: float, amount: float, newBalance: float}
     */
    private function deductUserPoints(User $user, float $totalPrice): array
    {
        $previousBalance = $user->points;
        $amount = -$totalPrice;
        $newBalance = $previousBalance + $amount;

        $user->update(['points' => $newBalance]);

        return [
            'previousBalance' => $previousBalance,
            'amount' => $amount,
            'newBalance' => $newBalance,
        ];
    }

    /**
     * Create purchase and transaction records.
     *
     * @param User $user
     * @param ShopItem $shopItem
     * @param float $totalPrice
     * @param int $quantity
     * @param array{previousBalance: float, amount: float, newBalance: float} $pointsData
     * @return array{purchase: Purchase, transaction: PointTransaction}
     */
    private function createPurchaseRecords(
        User $user,
        ShopItem $shopItem,
        float $totalPrice,
        int $quantity,
        array $pointsData
    ): array {
        $purchase = Purchase::create([
            'user_id' => $user->id,
            'shop_item_id' => $shopItem->id,
            'price_paid' => $totalPrice,
            'quantity' => $quantity,
        ]);

        $transaction = PointTransaction::create([
            'user_id' => $user->id,
            'shop_item_id' => $shopItem->id,
            'amount' => $pointsData['amount'],
            'previous_balance' => $pointsData['previousBalance'],
            'new_balance' => $pointsData['newBalance'],
            'reason' => 'shop_purchase',
        ]);

        return [
            'purchase' => $purchase,
            'transaction' => $transaction,
        ];
    }
}
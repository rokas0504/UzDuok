<?php

declare(strict_types=1);

namespace App\Services\Points;

use App\Models\Points\PointTransaction;
use App\Models\Shop\Purchase;
use App\Models\Shop\ShopItem;
use App\Models\Tasks\Task;
use App\Models\Users\User;
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
     * @throws \Exception
     */
    public function purchaseShopItem(User $user, ShopItem $shopItem, int $quantity = 1): array
    {
        return DB::transaction(function () use ($user, $shopItem, $quantity) {
            $totalPrice = $shopItem->price * $quantity;

            if ($user->points < $totalPrice) {
                throw new \Exception('Insufficient points');
            }

            if ($shopItem->quantity < $quantity) {
                throw new \Exception('Insufficient shop item quantity');
            }

            $previousBalance = $user->points;
            $amount = -$totalPrice;
            $newBalance = $previousBalance + $amount;

            $user->update(['points' => $newBalance]);

            $shopItem->decrement('quantity', $quantity);

            $purchase = Purchase::create([
                'user_id' => $user->id,
                'shop_item_id' => $shopItem->id,
                'price_paid' => $totalPrice,
                'quantity' => $quantity,
            ]);

            $transaction = PointTransaction::create([
                'user_id' => $user->id,
                'shop_item_id' => $shopItem->id,
                'amount' => $amount,
                'previous_balance' => $previousBalance,
                'new_balance' => $newBalance,
                'reason' => 'shop_purchase',
            ]);

            return [
                'purchase' => $purchase,
                'transaction' => $transaction,
            ];
        });
    }

    public function deductPoints(User $user, float $points, string $reason): PointTransaction
    {
        if ($points <= 0) {
            throw new \Exception('Vaikas nebeturi taškų kuriuos galima minusuoti');
        }

        return DB::transaction(function () use ($user, $points, $reason) {
            $previousBalance = $user->points;
            $amount = -$points;
            $newBalance = max(0, $previousBalance + $amount);

            // Check if deduction would result in negative balance
            if ($newBalance < 0) {
                throw new \Exception('Per daug taškų numinusuota');
            }

            $user->update(['points' => $newBalance]);

            return PointTransaction::create([
                'user_id' => $user->id,
                'task_id' => null,
                'shop_item_id' => null,
                'amount' => $amount,
                'previous_balance' => $previousBalance,
                'new_balance' => $newBalance,
                'reason' => $reason,
            ]);
        });
    }
}
<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Points\PointTransaction;
use App\Models\Roles\Role;
use App\Models\Shop\Purchase;
use App\Models\Shop\ShopItem;
use App\Models\Tasks\Task;
use App\Models\Users\User;
use App\Services\Points\PointService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PointServiceTest extends TestCase
{
    use RefreshDatabase;

    private PointService $pointService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pointService = new PointService();
    }

    /**
     * Test addPointsForTaskCompletion adds points correctly
     */
    public function test_addPointsForTaskCompletion_adds_points_and_creates_transaction(): void
    {
        // Arrange
        $childRole = Role::factory()->create(['slug' => 'child']);
        $user = User::factory()->create([
            'role_id' => $childRole->id,
            'points' => 50,
        ]);
        $task = Task::factory()->create([
            'user_id' => $user->id,
            'price' => 25,
        ]);

        // Act
        $transaction = $this->pointService->addPointsForTaskCompletion($user, $task);

        // Assert
        $this->assertInstanceOf(PointTransaction::class, $transaction);
        $this->assertEquals($user->id, $transaction->user_id);
        $this->assertEquals($task->id, $transaction->task_id);
        $this->assertEquals(25, $transaction->amount);
        $this->assertEquals(50, $transaction->previous_balance);
        $this->assertEquals(75, $transaction->new_balance);
        $this->assertEquals('task_completed', $transaction->reason);

        // Verify user points updated
        $user->refresh();
        $this->assertEquals(75, $user->points);
    }

    /**
     * Test subtractPointsForTaskCancellation subtracts points correctly
     */
    public function test_subtractPointsForTaskCancellation_subtracts_points(): void
    {
        // Arrange
        $childRole = Role::factory()->create(['slug' => 'child']);
        $user = User::factory()->create([
            'role_id' => $childRole->id,
            'points' => 100,
        ]);
        $task = Task::factory()->create([
            'user_id' => $user->id,
            'price' => 30,
        ]);

        // Act
        $transaction = $this->pointService->subtractPointsForTaskCancellation($user, $task);

        // Assert
        $this->assertInstanceOf(PointTransaction::class, $transaction);
        $this->assertEquals($user->id, $transaction->user_id);
        $this->assertEquals($task->id, $transaction->task_id);
        $this->assertEquals(-30, $transaction->amount); // Negative
        $this->assertEquals(100, $transaction->previous_balance);
        $this->assertEquals(70, $transaction->new_balance);
        $this->assertEquals('task_cancelled', $transaction->reason);

        // Verify user points updated
        $user->refresh();
        $this->assertEquals(70, $user->points);
    }

    /**
     * Test subtractPointsForTaskCancellation cannot go below zero
     */
    public function test_subtractPointsForTaskCancellation_cannot_go_below_zero(): void
    {
        // Arrange
        $childRole = Role::factory()->create(['slug' => 'child']);
        $user = User::factory()->create([
            'role_id' => $childRole->id,
            'points' => 10, // Only 10 points
        ]);
        $task = Task::factory()->create([
            'user_id' => $user->id,
            'price' => 50, // Trying to subtract 50
        ]);

        // Act
        $transaction = $this->pointService->subtractPointsForTaskCancellation($user, $task);

        // Assert
        $this->assertEquals(-50, $transaction->amount);
        $this->assertEquals(10, $transaction->previous_balance);
        $this->assertEquals(0, $transaction->new_balance); // Should be 0, not -40
        $this->assertEquals('task_cancelled', $transaction->reason);

        // Verify user points cannot be negative
        $user->refresh();
        $this->assertEquals(0, $user->points);
    }

    /**
     * Test purchaseShopItem succeeds with sufficient points and quantity
     */
    public function test_purchaseShopItem_succeeds_with_sufficient_points_and_quantity(): void
    {
        // Arrange
        $childRole = Role::factory()->create(['slug' => 'child']);
        $user = User::factory()->create([
            'role_id' => $childRole->id,
            'points' => 100,
        ]);
        $parentRole = Role::factory()->create(['slug' => 'parent']);
        $creator = User::factory()->create(['role_id' => $parentRole->id]);
        $shopItem = ShopItem::factory()->create([
            'created_by' => $creator->id,
            'price' => 30,
            'quantity' => 5,
        ]);

        // Act
        $result = $this->pointService->purchaseShopItem($user, $shopItem, 2);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('purchase', $result);
        $this->assertArrayHasKey('transaction', $result);

        // Verify purchase
        $purchase = $result['purchase'];
        $this->assertInstanceOf(Purchase::class, $purchase);
        $this->assertEquals($user->id, $purchase->user_id);
        $this->assertEquals($shopItem->id, $purchase->shop_item_id);
        $this->assertEquals(60, $purchase->price_paid); // 30 * 2
        $this->assertEquals(2, $purchase->quantity);

        // Verify transaction
        $transaction = $result['transaction'];
        $this->assertInstanceOf(PointTransaction::class, $transaction);
        $this->assertEquals($user->id, $transaction->user_id);
        $this->assertEquals($shopItem->id, $transaction->shop_item_id);
        $this->assertEquals(-60, $transaction->amount); // Negative
        $this->assertEquals(100, $transaction->previous_balance);
        $this->assertEquals(40, $transaction->new_balance);
        $this->assertEquals('shop_purchase', $transaction->reason);

        // Verify user points deducted
        $user->refresh();
        $this->assertEquals(40, $user->points);

        // Verify shop item quantity decremented
        $shopItem->refresh();
        $this->assertEquals(3, $shopItem->quantity); // 5 - 2
    }

    /**
     * Test purchaseShopItem fails with insufficient points
     */
    public function test_purchaseShopItem_fails_with_insufficient_points(): void
    {
        // Arrange
        $childRole = Role::factory()->create(['slug' => 'child']);
        $user = User::factory()->create([
            'role_id' => $childRole->id,
            'points' => 20, // Not enough
        ]);
        $parentRole = Role::factory()->create(['slug' => 'parent']);
        $creator = User::factory()->create(['role_id' => $parentRole->id]);
        $shopItem = ShopItem::factory()->create([
            'created_by' => $creator->id,
            'price' => 30,
            'quantity' => 5,
        ]);

        // Act & Assert
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Insufficient points');

        $this->pointService->purchaseShopItem($user, $shopItem, 1);

        // Verify nothing changed
        $user->refresh();
        $this->assertEquals(20, $user->points);

        $shopItem->refresh();
        $this->assertEquals(5, $shopItem->quantity);
    }

    /**
     * Test purchaseShopItem fails with insufficient shop item quantity
     */
    public function test_purchaseShopItem_fails_with_insufficient_quantity(): void
    {
        // Arrange
        $childRole = Role::factory()->create(['slug' => 'child']);
        $user = User::factory()->create([
            'role_id' => $childRole->id,
            'points' => 200, // Enough points
        ]);
        $parentRole = Role::factory()->create(['slug' => 'parent']);
        $creator = User::factory()->create(['role_id' => $parentRole->id]);
        $shopItem = ShopItem::factory()->create([
            'created_by' => $creator->id,
            'price' => 30,
            'quantity' => 2, // Only 2 available
        ]);

        // Act & Assert
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Insufficient shop item quantity');

        $this->pointService->purchaseShopItem($user, $shopItem, 5); // Trying to buy 5

        // Verify nothing changed
        $user->refresh();
        $this->assertEquals(200, $user->points);

        $shopItem->refresh();
        $this->assertEquals(2, $shopItem->quantity);
    }

    /**
     * Test purchaseShopItem with quantity = 1 (default)
     */
    public function test_purchaseShopItem_default_quantity_is_one(): void
    {
        // Arrange
        $childRole = Role::factory()->create(['slug' => 'child']);
        $user = User::factory()->create([
            'role_id' => $childRole->id,
            'points' => 100,
        ]);
        $parentRole = Role::factory()->create(['slug' => 'parent']);
        $creator = User::factory()->create(['role_id' => $parentRole->id]);
        $shopItem = ShopItem::factory()->create([
            'created_by' => $creator->id,
            'price' => 40,
            'quantity' => 10,
        ]);

        // Act
        $result = $this->pointService->purchaseShopItem($user, $shopItem); // No quantity param

        // Assert
        $purchase = $result['purchase'];
        $this->assertEquals(1, $purchase->quantity);
        $this->assertEquals(40, $purchase->price_paid);

        $user->refresh();
        $this->assertEquals(60, $user->points); // 100 - 40

        $shopItem->refresh();
        $this->assertEquals(9, $shopItem->quantity); // 10 - 1
    }

    /**
     * Test purchaseShopItem transaction rollback on exception
     */
    public function test_purchaseShopItem_transaction_rollback_on_exception(): void
    {
        // Arrange
        $childRole = Role::factory()->create(['slug' => 'child']);
        $user = User::factory()->create([
            'role_id' => $childRole->id,
            'points' => 50,
        ]);
        $parentRole = Role::factory()->create(['slug' => 'parent']);
        $creator = User::factory()->create(['role_id' => $parentRole->id]);
        $shopItem = ShopItem::factory()->create([
            'created_by' => $creator->id,
            'price' => 30,
            'quantity' => 1,
        ]);

        // Act - Trying to buy with insufficient points should rollback everything
        try {
            $this->pointService->purchaseShopItem($user, $shopItem, 2); // Need 60, have 50
            $this->fail('Expected exception was not thrown');
        } catch (Exception $e) {
            $this->assertEquals('Insufficient points', $e->getMessage());
        }

        // Assert - Nothing should have changed due to transaction rollback
        $user->refresh();
        $this->assertEquals(50, $user->points); // Still 50

        $shopItem->refresh();
        $this->assertEquals(1, $shopItem->quantity); // Still 1

        // No purchase or transaction records created
        $this->assertEquals(0, Purchase::count());
        $this->assertEquals(0, PointTransaction::count());
    }
}
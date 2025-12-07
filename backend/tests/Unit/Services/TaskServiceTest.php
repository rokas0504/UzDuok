<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Enums\TaskStatus;
use App\Models\Roles\Role;
use App\Models\Tasks\Task;
use App\Models\Users\User;
use App\Repositories\Tasks\TaskRepository;
use App\Services\Points\PointService;
use App\Services\Tasks\TaskService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class TaskServiceTest extends TestCase
{
    use RefreshDatabase;

    private TaskService $taskService;
    private TaskRepository $taskRepository;
    private PointService $pointService;

    protected function setUp(): void
    {
        parent::setUp();

        // Create mock dependencies
        $this->taskRepository = Mockery::mock(TaskRepository::class);
        $this->pointService = Mockery::mock(PointService::class);

        // Create service instance with mocks
        $this->taskService = new TaskService(
            $this->taskRepository,
            $this->pointService
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test updateStatus returns false when repository update fails
     */
    public function test_updateStatus_returns_false_when_update_fails(): void
    {
        // Arrange
        $task = Mockery::mock(Task::class);
        $task->status = TaskStatus::IN_PROGRESS->value;

        $this->taskRepository
            ->shouldReceive('updateStatus')
            ->once()
            ->with($task, TaskStatus::COMPLETED->value)
            ->andReturn(false);

        // Act
        $result = $this->taskService->updateStatus($task, TaskStatus::COMPLETED->value);

        // Assert
        $this->assertFalse($result);
    }

    /**
     * Test updateStatus succeeds but user is not a child (no points awarded)
     */
    public function test_updateStatus_succeeds_but_user_is_not_child(): void
    {
        // Arrange: Create real models
        $parentRole = Role::factory()->create(['slug' => 'parent']);
        $user = User::factory()->create(['role_id' => $parentRole->id, 'points' => 0]);
        $task = Task::factory()->create([
            'user_id' => $user->id,
            'status' => TaskStatus::IN_PROGRESS->value,
            'price' => 10,
        ]);

        // Use real repository and service
        $realRepository = new TaskRepository();
        $mockPointService = Mockery::mock(PointService::class);

        // Point service should NOT be called for parent users
        $mockPointService->shouldNotReceive('addPointsForTaskCompletion');
        $mockPointService->shouldNotReceive('subtractPointsForTaskCancellation');

        $service = new TaskService($realRepository, $mockPointService);

        // Act
        $result = $service->updateStatus($task, TaskStatus::COMPLETED->value);

        // Assert
        $this->assertTrue($result);
        $task->refresh();
        $this->assertEquals(TaskStatus::COMPLETED->value, $task->status);
    }

    /**
     * Test updateStatus with child user completing task - points should be added
     */
    public function test_updateStatus_child_user_completes_task_adds_points(): void
    {
        // Arrange: Create child user
        $childRole = Role::factory()->create(['slug' => 'child']);
        $user = User::factory()->create(['role_id' => $childRole->id, 'points' => 0]);
        $task = Task::factory()->create([
            'user_id' => $user->id,
            'status' => TaskStatus::IN_PROGRESS->value,
            'price' => 10,
        ]);

        // Use real repository and mock point service
        $realRepository = new TaskRepository();
        $mockPointService = Mockery::mock(PointService::class);

        // Expect point service to be called
        $mockPointService
            ->shouldReceive('addPointsForTaskCompletion')
            ->once()
            ->with(Mockery::on(function ($arg) use ($user) {
                return $arg->id === $user->id;
            }), Mockery::on(function ($arg) use ($task) {
                return $arg->id === $task->id;
            }));

        $service = new TaskService($realRepository, $mockPointService);

        // Act
        $result = $service->updateStatus($task, TaskStatus::COMPLETED->value);

        // Assert
        $this->assertTrue($result);
        $task->refresh();
        $this->assertEquals(TaskStatus::COMPLETED->value, $task->status);
    }

    /**
     * Test updateStatus with child user cancelling task - points should be subtracted
     */
    public function test_updateStatus_child_user_cancels_task_subtracts_points(): void
    {
        // Arrange: Create child user
        $childRole = Role::factory()->create(['slug' => 'child']);
        $user = User::factory()->create(['role_id' => $childRole->id, 'points' => 50]);
        $task = Task::factory()->create([
            'user_id' => $user->id,
            'status' => TaskStatus::COMPLETED->value,
            'price' => 10,
        ]);

        // Use real repository and mock point service
        $realRepository = new TaskRepository();
        $mockPointService = Mockery::mock(PointService::class);

        // Expect point service to be called
        $mockPointService
            ->shouldReceive('subtractPointsForTaskCancellation')
            ->once()
            ->with(Mockery::on(function ($arg) use ($user) {
                return $arg->id === $user->id;
            }), Mockery::on(function ($arg) use ($task) {
                return $arg->id === $task->id;
            }));

        $service = new TaskService($realRepository, $mockPointService);

        // Act
        $result = $service->updateStatus($task, TaskStatus::CANCELLED->value);

        // Assert
        $this->assertTrue($result);
        $task->refresh();
        $this->assertEquals(TaskStatus::CANCELLED->value, $task->status);
    }

    /**
     * Test updateStatus with child user but status is not COMPLETED or CANCELLED
     * (no points operation should happen)
     */
    public function test_updateStatus_child_user_other_status_no_points_operation(): void
    {
        // Arrange: Create child user
        $childRole = Role::factory()->create(['slug' => 'child']);
        $user = User::factory()->create(['role_id' => $childRole->id, 'points' => 0]);
        $task = Task::factory()->create([
            'user_id' => $user->id,
            'status' => TaskStatus::IN_PROGRESS->value,
            'price' => 10,
        ]);

        // Use real repository and mock point service
        $realRepository = new TaskRepository();
        $mockPointService = Mockery::mock(PointService::class);

        // Point service should NOT be called for other statuses
        $mockPointService->shouldNotReceive('addPointsForTaskCompletion');
        $mockPointService->shouldNotReceive('subtractPointsForTaskCancellation');

        $service = new TaskService($realRepository, $mockPointService);

        // Act - changing from IN_PROGRESS to IN_PROGRESS (no-op status change)
        $result = $service->updateStatus($task, TaskStatus::IN_PROGRESS->value);

        // Assert
        $this->assertTrue($result);
    }

    /**
     * Test store method creates task with correct status
     */
    public function test_store_creates_task_with_in_progress_status(): void
    {
        // Arrange
        $data = [
            'title' => 'Test Task',
            'description' => 'Test Description',
            'start_date' => '2025-12-07',
            'end_date' => '2025-12-08',
            'user_id' => 1,
            'price' => 10,
            'selected_weekdays' => [0, 1],
            'weeks_count' => 2,
        ];

        $expectedData = $data;
        $expectedData['status'] = TaskStatus::IN_PROGRESS->value;
        unset($expectedData['selected_weekdays'], $expectedData['weeks_count']);

        $mockTask = Mockery::mock(Task::class);

        $this->taskRepository
            ->shouldReceive('store')
            ->once()
            ->with($expectedData)
            ->andReturn($mockTask);

        // Act
        $result = $this->taskService->store($data);

        // Assert
        $this->assertSame($mockTask, $result);
    }

    /**
     * Test createTask calls store for non-periodic task
     */
    public function test_createTask_calls_store_for_non_periodic_task(): void
    {
        // Arrange
        $data = [
            'title' => 'Test Task',
            'user_id' => 1,
            'price' => 10,
        ];

        $mockTask = Mockery::mock(Task::class);

        $this->taskRepository
            ->shouldReceive('store')
            ->once()
            ->andReturn($mockTask);

        // Act
        $result = $this->taskService->createTask($data);

        // Assert
        $this->assertSame($mockTask, $result);
    }

    /**
     * Test createPeriodicTasks creates multiple tasks
     */
    public function test_createPeriodicTasks_creates_multiple_tasks(): void
    {
        // Arrange
        $childRole = Role::factory()->create(['slug' => 'child']);
        $user = User::factory()->create(['role_id' => $childRole->id]);

        $data = [
            'title' => 'Periodic Task',
            'description' => 'Test',
            'user_id' => $user->id,
            'price' => 10,
            'is_repeated' => true,
            'selected_weekdays' => [0, 2], // Monday, Wednesday
            'weeks_count' => 2,
            'start_date' => '2025-12-08', // Monday
        ];

        $realRepository = new TaskRepository();
        $mockPointService = Mockery::mock(PointService::class);
        $service = new TaskService($realRepository, $mockPointService);

        // Act
        $result = $service->createTask($data);

        // Assert
        $this->assertIsArray($result);
        // Should create 4 tasks: Week 1 (Mon, Wed) + Week 2 (Mon, Wed)
        $this->assertCount(4, $result);

        // Verify all tasks are created with correct dates
        foreach ($result as $task) {
            $this->assertInstanceOf(Task::class, $task);
            $this->assertEquals(TaskStatus::IN_PROGRESS->value, $task->status);
            $this->assertEquals('Periodic Task', $task->title);
        }
    }
}
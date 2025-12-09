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

/**
 * Unit tests for TaskService periodic task creation functionality.
 */
class TaskServicePeriodicTest extends TestCase
{
    use RefreshDatabase;

    private TaskService $taskService;
    private TaskRepository $taskRepository;
    private PointService $pointService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->taskRepository = new TaskRepository();
        $this->pointService = Mockery::mock(PointService::class);
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
     * Test createPeriodicTasks creates multiple tasks for selected weekdays and weeks.
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

        // Act
        $result = $this->taskService->createTask($data);

        // Assert
        $this->assertIsArray($result);
        // Should create 4 tasks: Week 1 (Mon, Wed) + Week 2 (Mon, Wed)
        $this->assertCount(4, $result);

        foreach ($result as $task) {
            $this->assertInstanceOf(Task::class, $task);
            $this->assertEquals(TaskStatus::IN_PROGRESS, $task->status);
            $this->assertEquals('Periodic Task', $task->title);
        }
    }

    /**
     * Test createPeriodicTasks creates correct dates for each weekday.
     */
    public function test_createPeriodicTasks_creates_correct_dates(): void
    {
        // Arrange
        $childRole = Role::factory()->create(['slug' => 'child']);
        $user = User::factory()->create(['role_id' => $childRole->id]);

        $data = [
            'title' => 'Date Test Task',
            'description' => 'Test',
            'user_id' => $user->id,
            'price' => 15,
            'is_repeated' => true,
            'selected_weekdays' => [0], // Monday only
            'weeks_count' => 3,
            'start_date' => '2025-12-08', // Monday
        ];

        // Act
        $result = $this->taskService->createTask($data);

        // Assert
        $this->assertCount(3, $result);

        // Verify dates are consecutive Mondays
        $expectedDates = ['2025-12-08', '2025-12-15', '2025-12-22'];
        foreach ($result as $index => $task) {
            $this->assertEquals($expectedDates[$index], $task->start_date->format('Y-m-d'));
            $this->assertEquals($expectedDates[$index], $task->end_date->format('Y-m-d'));
        }
    }

    /**
     * Test createPeriodicTasks skips dates before start_date.
     */
    public function test_createPeriodicTasks_skips_dates_before_start_date(): void
    {
        // Arrange
        $childRole = Role::factory()->create(['slug' => 'child']);
        $user = User::factory()->create(['role_id' => $childRole->id]);

        // Start on Wednesday, but select Monday and Wednesday
        // First week Monday should be skipped (before start_date)
        $data = [
            'title' => 'Skip Date Task',
            'description' => 'Test',
            'user_id' => $user->id,
            'price' => 10,
            'is_repeated' => true,
            'selected_weekdays' => [0, 2], // Monday (0), Wednesday (2)
            'weeks_count' => 2,
            'start_date' => '2025-12-10', // Wednesday
        ];

        // Act
        $result = $this->taskService->createTask($data);

        // Assert
        // Week 1: Wednesday only (Monday is before start_date)
        // Week 2: Monday and Wednesday
        $this->assertCount(3, $result);

        $dates = array_map(fn($task) => $task->start_date->format('Y-m-d'), $result);
        $this->assertContains('2025-12-10', $dates); // Week 1 Wednesday
        $this->assertContains('2025-12-15', $dates); // Week 2 Monday
        $this->assertContains('2025-12-17', $dates); // Week 2 Wednesday
        $this->assertNotContains('2025-12-08', $dates); // Week 1 Monday should be skipped
    }

    /**
     * Test createPeriodicTasks with single weekday and single week.
     */
    public function test_createPeriodicTasks_single_weekday_single_week(): void
    {
        // Arrange
        $childRole = Role::factory()->create(['slug' => 'child']);
        $user = User::factory()->create(['role_id' => $childRole->id]);

        $data = [
            'title' => 'Single Task',
            'description' => 'Test',
            'user_id' => $user->id,
            'price' => 5,
            'is_repeated' => true,
            'selected_weekdays' => [4], // Friday
            'weeks_count' => 1,
            'start_date' => '2025-12-08', // Monday
        ];

        // Act
        $result = $this->taskService->createTask($data);

        // Assert
        $this->assertCount(1, $result);
        $this->assertEquals('2025-12-12', $result[0]->start_date->format('Y-m-d')); // Friday of that week
    }

    /**
     * Test createPeriodicTasks preserves task data (title, description, price, user_id).
     */
    public function test_createPeriodicTasks_preserves_task_data(): void
    {
        // Arrange
        $childRole = Role::factory()->create(['slug' => 'child']);
        $user = User::factory()->create(['role_id' => $childRole->id]);

        $data = [
            'title' => 'Preserved Data Task',
            'description' => 'Important description',
            'user_id' => $user->id,
            'price' => 25,
            'is_repeated' => true,
            'selected_weekdays' => [1, 3], // Tuesday, Thursday
            'weeks_count' => 1,
            'start_date' => '2025-12-08',
        ];

        // Act
        $result = $this->taskService->createTask($data);

        // Assert
        $this->assertCount(2, $result);

        foreach ($result as $task) {
            $this->assertEquals('Preserved Data Task', $task->title);
            $this->assertEquals('Important description', $task->description);
            $this->assertEquals($user->id, $task->user_id);
            $this->assertEquals(25, $task->price);
        }
    }

    /**
     * Test createPeriodicTasks with all weekdays selected.
     */
    public function test_createPeriodicTasks_all_weekdays(): void
    {
        // Arrange
        $childRole = Role::factory()->create(['slug' => 'child']);
        $user = User::factory()->create(['role_id' => $childRole->id]);

        $data = [
            'title' => 'Daily Task',
            'description' => 'Every day',
            'user_id' => $user->id,
            'price' => 5,
            'is_repeated' => true,
            'selected_weekdays' => [0, 1, 2, 3, 4, 5, 6], // All days Mon-Sun
            'weeks_count' => 1,
            'start_date' => '2025-12-08', // Monday
        ];

        // Act
        $result = $this->taskService->createTask($data);

        // Assert - 7 days in one week
        $this->assertCount(7, $result);

        // Verify all days are present
        $dates = array_map(fn($task) => $task->start_date->format('Y-m-d'), $result);
        $expectedDates = [
            '2025-12-08', // Monday
            '2025-12-09', // Tuesday
            '2025-12-10', // Wednesday
            '2025-12-11', // Thursday
            '2025-12-12', // Friday
            '2025-12-13', // Saturday
            '2025-12-14', // Sunday
        ];

        foreach ($expectedDates as $date) {
            $this->assertContains($date, $dates);
        }
    }

    /**
     * Test createPeriodicTasks removes periodic fields from created tasks.
     */
    public function test_createPeriodicTasks_removes_periodic_fields(): void
    {
        // Arrange
        $childRole = Role::factory()->create(['slug' => 'child']);
        $user = User::factory()->create(['role_id' => $childRole->id]);

        $data = [
            'title' => 'Clean Task',
            'description' => 'No periodic fields',
            'user_id' => $user->id,
            'price' => 10,
            'is_repeated' => true,
            'selected_weekdays' => [0],
            'weeks_count' => 1,
            'start_date' => '2025-12-08',
        ];

        // Act
        $result = $this->taskService->createTask($data);

        // Assert
        $this->assertCount(1, $result);
        $task = $result[0];

        // Task should not have periodic fields stored
        $this->assertNull($task->selected_weekdays ?? null);
        $this->assertNull($task->weeks_count ?? null);
    }
}

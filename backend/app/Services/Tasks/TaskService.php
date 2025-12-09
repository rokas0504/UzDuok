<?php

declare(strict_types=1);

namespace App\Services\Tasks;

use App\Enums\TaskStatus;
use App\Models\Tasks\Task;
use App\Repositories\Repository;
use App\Repositories\Tasks\TaskRepository;
use App\Services\Points\PointService;
use App\Services\Service;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class TaskService extends Service
{
    public function __construct(
        private readonly TaskRepository $taskRepository,
        private readonly PointService $pointService
    ) {
        $this->repository = $taskRepository;
    }

    /**
     * Create a new task for a user.
     *
     * @param array $data
     * @return Model
     */
    public function store(array $data): Model
    {
        $data['status'] = TaskStatus::IN_PROGRESS->value;

        // Remove periodic fields before storing single task
        unset($data['selected_weekdays'], $data['weeks_count']);

        return $this->repository->store($data);
    }

    /**
     * Create task(s) - handles both single and periodic task creation.
     *
     * @param array $data
     * @return Model|array Returns single task or array of tasks for periodic creation
     */
    public function createTask(array $data): Model|array
    {
        // Check if this is a periodic task creation
        if (!empty($data['is_repeated']) && !empty($data['selected_weekdays']) && !empty($data['weeks_count'])) {
            return $this->createPeriodicTasks($data);
        }

        return $this->store($data);
    }

    /**
     * Create multiple tasks based on selected weekdays and number of weeks.
     * 
     * Refactored applying:
     * - SRP: Separated date generation from task creation
     * - Extract Method: generateTaskDates() handles date logic
     * - GRASP Pure Fabrication: Date calculation isolated
     *
     * @param array $data
     * @return array Array of created tasks
     */
    private function createPeriodicTasks(array $data): array
    {
        $taskDates = $this->generateTaskDates(
            Carbon::parse($data['start_date']),
            $data['selected_weekdays'],
            $data['weeks_count']
        );

        $baseTaskData = $this->prepareBaseTaskData($data);

        return $this->createTasksForDates($baseTaskData, $taskDates);
    }

    /**
     * Generate all valid task dates based on weekdays and weeks count.
     * (SRP: Single responsibility - only date generation)
     *
     * @param Carbon $startDate
     * @param array $selectedWeekdays 0 = Monday, 6 = Sunday
     * @param int $weeksCount
     * @return array<Carbon>
     */
    private function generateTaskDates(Carbon $startDate, array $selectedWeekdays, int $weeksCount): array
    {
        $dates = [];
        $weekStart = $startDate->copy()->startOfWeek(Carbon::MONDAY);

        for ($week = 0; $week < $weeksCount; $week++) {
            $currentWeekStart = $weekStart->copy()->addWeeks($week);
            
            foreach ($selectedWeekdays as $weekday) {
                $taskDate = $currentWeekStart->copy()->addDays($weekday);
                
                if ($taskDate->gte($startDate)) {
                    $dates[] = $taskDate;
                }
            }
        }

        return $dates;
    }

    /**
     * Prepare base task data by removing periodic fields and setting status.
     * (SRP: Single responsibility - only data preparation)
     *
     * @param array $data
     * @return array
     */
    private function prepareBaseTaskData(array $data): array
    {
        unset($data['selected_weekdays'], $data['weeks_count']);
        $data['status'] = TaskStatus::IN_PROGRESS->value;

        return $data;
    }

    /**
     * Create tasks for each provided date.
     * (SRP: Single responsibility - only task creation)
     *
     * @param array $baseTaskData
     * @param array<Carbon> $dates
     * @return array<Model>
     */
    private function createTasksForDates(array $baseTaskData, array $dates): array
    {
        return array_map(function (Carbon $date) use ($baseTaskData) {
            $taskData = $baseTaskData;
            $taskData['start_date'] = $date->format('Y-m-d');
            $taskData['end_date'] = $date->format('Y-m-d');

            return $this->repository->store($taskData);
        }, $dates);
    }

    /**
     * Get all tasks for a specific user.
     *
     * @param int $userId
     * @return Collection
     */
    public function getTasksByUserId(int $userId): Collection
    {
        return $this->repository->getTasksByUserId($userId);
    }

    /**
     * Get tasks by status for a specific user.
     *
     * @param int $userId
     * @param string $status
     * @return Collection
     */
    public function getTasksByStatus(int $userId, string $status): Collection
    {
        return $this->repository->getTasksByStatus($userId, $status);
    }

    /**
     * Update task status.
     *
     * @param Task $task
     * @param string $status
     * @return bool
     */
    public function updateStatus(Task $task, string $status): bool
    {
        $result = $this->repository->updateStatus($task, $status);

        if ($result) {
            $task->refresh();
            $this->handlePointsForStatusChange($task);
        }

        return $result;
    }

    /**
     * Handle points adjustment based on task status change.
     *
     * @param Task $task
     * @return void
     */
    private function handlePointsForStatusChange(Task $task): void
    {
        $task->load(['user.role']);
        $user = $task->user;

        if (!$user || !$user->isChild()) {
            return;
        }

        match ($task->status) {
            TaskStatus::COMPLETED->value => $this->pointService->addPointsForTaskCompletion($user, $task),
            TaskStatus::CANCELLED->value => $this->pointService->subtractPointsForTaskCancellation($user, $task),
            default => null,
        };
    }
}

<?php

declare(strict_types=1);

namespace App\Services\Tasks;

use App\Enums\TaskStatus;
use App\Models\Tasks\Task;
use App\Repositories\Repository;
use App\Repositories\Tasks\TaskRepository;
use App\Services\Service;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class TaskService extends Service
{
    public function __construct(
        private readonly TaskRepository $taskRepository
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
     * @param array $data
     * @return array Array of created tasks
     */
    private function createPeriodicTasks(array $data): array
    {
        $selectedWeekdays = $data['selected_weekdays']; // 0 = Monday, 6 = Sunday
        $weeksCount = $data['weeks_count'];
        $startDate = Carbon::parse($data['start_date']);

        // Remove periodic-specific fields from task data
        unset($data['selected_weekdays'], $data['weeks_count']);

        // Set status for all tasks
        $data['status'] = TaskStatus::IN_PROGRESS->value;

        $tasks = [];

        // For each week
        for ($week = 0; $week < $weeksCount; $week++) {
            // For each selected weekday
            foreach ($selectedWeekdays as $weekday) {
                // Get the start of the week (Monday) and add the week offset
                $weekStart = $startDate->copy()->startOfWeek(Carbon::MONDAY)->addWeeks($week);

                // Get the specific day of that week
                $taskDate = $weekStart->copy()->addDays($weekday);

                // Only create tasks for dates on or after the start date
                if ($taskDate->gte($startDate)) {
                    $taskData = $data;
                    $taskData['start_date'] = $taskDate->format('Y-m-d');
                    $taskData['end_date'] = $taskDate->format('Y-m-d');

                    $tasks[] = $this->repository->store($taskData);
                }
            }
        }

        return $tasks;
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
        return $this->repository->updateStatus($task, $status);
    }
}

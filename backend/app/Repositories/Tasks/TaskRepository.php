<?php

namespace App\Repositories\Tasks;

use App\Enums\TaskStatus;
use App\Models\Tasks\Task;
use App\Repositories\Repository;
use Illuminate\Database\Eloquent\Collection;

class TaskRepository extends Repository
{
    public function __construct()
    {
        $this->model = new Task();
    }

    /**
     * Get all tasks for a specific user.
     *
     * @param int $userId
     * @return Collection
     */
    public function getTasksByUserId(int $userId): Collection
    {
        $this->initializeQuery();

        return $this->query
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
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
        $this->initializeQuery();

        return $this->query
            ->where('user_id', $userId)
            ->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->get();
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
        return $task->update(['status' => $status]);
    }
    
    public function getExpiredInProgressTasks(): Collection
    {
        $this->initializeQuery();

        return $this->query
            ->where('status', TaskStatus::IN_PROGRESS->value)
            ->where('end_date', '<', now())
            ->with(['user.role'])
            ->get();
    }
}
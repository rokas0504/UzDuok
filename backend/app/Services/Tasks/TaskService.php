<?php

declare(strict_types=1);

namespace App\Services\Tasks;

use App\Enums\TaskStatus;
use App\Models\Tasks\Task;
use App\Repositories\Repository;
use App\Repositories\Tasks\TaskRepository;
use App\Services\Service;
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

        return $this->repository->store($data);
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

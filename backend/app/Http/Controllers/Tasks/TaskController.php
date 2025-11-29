<?php

namespace App\Http\Controllers\Tasks;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tasks\StoreTaskRequest;
use App\Http\Requests\Tasks\UpdateTaskRequest;
use App\Models\Tasks\Task;
use App\Services\Tasks\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function __construct(
        private readonly TaskService $taskService
    ) {}

    /**
     * Display a listing of tasks for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $tasks = $this->taskService->getTasksByUserId($user->id);

        return response()->json([
            'tasks' => $tasks,
        ]);
    }

    /**
     * Store a newly created task.
     */
    public function store(StoreTaskRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;

        $task = $this->taskService->store($data);

        return response()->json([
            'message' => 'Task created successfully',
            'task' => $task,
        ], 201);
    }

    /**
     * Display the specified task.
     */
    public function show(Task $task): JsonResponse
    {
        return response()->json([
            'task' => $task,
        ]);
    }

    /**
     * Update the specified task.
     */
    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        $this->taskService->update($task, $request->validated());

        return response()->json([
            'message' => 'Task updated successfully',
            'task' => $task->fresh(),
        ]);
    }

    /**
     * Remove the specified task.
     */
    public function destroy(Task $task): JsonResponse
    {
        $this->taskService->destroy($task);

        return response()->json([
            'message' => 'Task deleted successfully',
        ]);
    }

    /**
     * Update the status of the specified task.
     */
    public function updateStatus(Request $request, Task $task): JsonResponse
    {
        $request->validate([
            'status' => ['required', 'string'],
        ]);

        $this->taskService->updateStatus($task, $request->status);

        return response()->json([
            'message' => 'Task status updated successfully',
            'task' => $task->fresh(),
        ]);
    }
}
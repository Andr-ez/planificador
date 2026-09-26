<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\Task;
use App\Models\TaskItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    public function store(Request $request, Subject $subject): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'due_date' => ['required', 'date'],
            'task_items' => ['required', 'array', 'min:1'],
            'task_items.*.description' => ['required', 'string', 'max:255'],
            'task_items.*.is_completed' => ['sometimes', 'boolean'],
        ]);

        $task = DB::transaction(function () use ($subject, $validated): Task {
            $task = $subject->tasks()->create([
                'title' => $validated['title'],
                'due_date' => $validated['due_date'],
                'status' => 'pending',
            ]);

            $task->taskItems()->createMany($validated['task_items']);

            $this->synchronizeStatus($task);

            return $task;
        });

        return response()->json($task->load('taskItems'), 201);
    }

    public function toggle(TaskItem $taskItem): JsonResponse
    {
        $taskItem = DB::transaction(function () use ($taskItem): TaskItem {
            $task = Task::query()->lockForUpdate()->findOrFail($taskItem->task_id);

            $taskItem->update(['is_completed' => ! $taskItem->is_completed]);
            $this->synchronizeStatus($task);

            return $taskItem;
        });

        return response()->json($taskItem->fresh());
    }

    public function addItem(Request $request, Task $task): JsonResponse
    {
        $validated = $request->validate([
            'description' => ['required', 'string'],
        ]);

        $taskItem = DB::transaction(function () use ($task, $validated): TaskItem {
            $task = Task::query()->lockForUpdate()->findOrFail($task->id);

            $taskItem = $task->taskItems()->create([
                'description' => $validated['description'],
            ]);

            $this->synchronizeStatus($task);

            return $taskItem;
        });

        return response()->json($taskItem, 201);
    }

    private function synchronizeStatus(Task $task): void
    {
        $status = $task->taskItems()
            ->where('is_completed', false)
            ->exists() ? 'pending' : 'completed';

        $task->update(['status' => $status]);
    }
}

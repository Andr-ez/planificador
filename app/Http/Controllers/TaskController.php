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
    public function index(Subject $subject): JsonResponse
    {
        $cutoff = now()->subDays(3);

        $tasks = $subject->tasks()
            ->with('taskItems')
            ->latest('due_date')
            ->get()
            ->each(function (Task $task) use ($cutoff): void {
                $task->setAttribute('is_inactive', $task->status === 'pending'
                    && $task->due_date->isFuture()
                    && $task->updated_at->lte($cutoff)
                    && ! $task->taskItems->contains(
                        fn (TaskItem $item): bool => $item->updated_at->gt($cutoff),
                    ));
            });

        return response()->json($tasks);
    }

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

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
                $task->setAttribute('progress', $this->calculateProgress($task));
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

        return response()->json($this->withProgress($task->load('taskItems')), 201);
    }

    public function toggle(TaskItem $taskItem): JsonResponse
    {
        $taskItem = DB::transaction(function () use ($taskItem): TaskItem {
            $task = Task::query()->lockForUpdate()->findOrFail($taskItem->task_id);
            $taskItem = TaskItem::query()->lockForUpdate()->findOrFail($taskItem->id);

            $taskItem->update(['is_completed' => ! $taskItem->is_completed]);
            $this->synchronizeStatus($task);

            return $taskItem;
        });

        return response()->json($taskItem->fresh());
    }

    public function addItem(Request $request, Task $task): JsonResponse
    {
        $validated = $request->validate([
            'description' => ['required', 'string', 'max:255'],
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
        $hasItems = $task->taskItems()->exists();
        $hasIncompleteItems = $task->taskItems()
            ->where('is_completed', false)
            ->exists();
        $status = $hasItems && ! $hasIncompleteItems ? 'completed' : 'pending';

        $task->update(['status' => $status]);
    }

    private function calculateProgress(Task $task): float
    {
        $totalItems = $task->taskItems->count();

        if ($totalItems === 0) {
            return 0.0;
        }

        return round(($task->taskItems->where('is_completed', true)->count() / $totalItems) * 100, 2);
    }

    private function withProgress(Task $task): Task
    {
        $task->loadMissing('taskItems');
        $task->setAttribute('progress', $this->calculateProgress($task));

        return $task;
    }
}

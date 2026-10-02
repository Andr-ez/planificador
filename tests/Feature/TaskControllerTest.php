<?php

namespace Tests\Feature;

use App\Models\Subject;
use App\Models\Task;
use App\Models\TaskItem;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class TaskControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_subject_detail_resolves_the_subject_model_for_the_view(): void
    {
        $subject = Subject::query()->create([
            'name' => 'Literatura',
            'credits' => 2,
        ]);

        $this->get("/subjects/{$subject->id}")
            ->assertOk()
            ->assertViewIs('subjects.show')
            ->assertViewHas('subject', $subject);
    }

    public function test_subject_detail_returns_not_found_for_an_unknown_subject(): void
    {
        $this->get('/subjects/999999')
            ->assertNotFound();
    }

    public function test_creates_a_task_with_its_initial_items_and_marks_it_completed_when_all_are_completed(): void
    {
        $subject = Subject::query()->create([
            'name' => 'Matemáticas',
            'credits' => 3,
        ]);

        $response = $this->postJson("/api/subjects/{$subject->id}/tasks", [
            'title' => 'Repasar álgebra',
            'due_date' => '2026-10-01 10:00:00',
            'task_items' => [
                ['description' => 'Leer el capítulo 1', 'is_completed' => true],
                ['description' => 'Resolver ejercicios', 'is_completed' => true],
            ],
        ]);

        $response->assertCreated()
            ->assertJsonPath('title', 'Repasar álgebra')
            ->assertJsonCount(2, 'task_items')
            ->assertJsonPath('progress', 100);

        $this->assertDatabaseHas('tasks', [
            'subject_id' => $subject->id,
            'title' => 'Repasar álgebra',
            'status' => 'completed',
        ]);
        $this->assertDatabaseCount('task_items', 2);
    }

    public function test_toggles_an_item_and_synchronizes_its_task_status(): void
    {
        $subject = Subject::query()->create([
            'name' => 'Física',
            'credits' => 4,
        ]);
        $task = Task::query()->create([
            'subject_id' => $subject->id,
            'title' => 'Preparar examen',
            'due_date' => '2026-10-01 10:00:00',
            'status' => 'pending',
        ]);
        $firstItem = TaskItem::query()->create([
            'task_id' => $task->id,
            'description' => 'Repasar teoría',
            'is_completed' => false,
        ]);
        $secondItem = TaskItem::query()->create([
            'task_id' => $task->id,
            'description' => 'Resolver problemas',
            'is_completed' => true,
        ]);

        $this->patchJson("/api/task-items/{$firstItem->id}/toggle")
            ->assertOk()
            ->assertJsonPath('id', $firstItem->id)
            ->assertJsonPath('is_completed', true);

        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'status' => 'completed']);

        $this->patchJson("/api/task-items/{$secondItem->id}/toggle")
            ->assertOk()
            ->assertJsonPath('id', $secondItem->id)
            ->assertJsonPath('is_completed', false);

        $this->getJson("/api/subjects/{$subject->id}/tasks")
            ->assertJsonPath('0.progress', 50);

        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'status' => 'pending']);
    }

    public function test_adds_a_pending_item_and_changes_a_completed_task_to_pending(): void
    {
        $subject = Subject::query()->create([
            'name' => 'Química',
            'credits' => 3,
        ]);
        $task = Task::query()->create([
            'subject_id' => $subject->id,
            'title' => 'Preparar laboratorio',
            'due_date' => '2026-10-02 10:00:00',
            'status' => 'completed',
        ]);
        TaskItem::query()->create([
            'task_id' => $task->id,
            'description' => 'Revisar protocolo',
            'is_completed' => true,
        ]);

        $this->postJson("/api/tasks/{$task->id}/items", [
            'description' => 'Comprar materiales',
        ])
            ->assertCreated()
            ->assertJsonPath('task_id', $task->id)
            ->assertJsonPath('description', 'Comprar materiales')
            ->assertJsonPath('is_completed', false);

        $this->assertDatabaseHas('task_items', [
            'task_id' => $task->id,
            'description' => 'Comprar materiales',
            'is_completed' => false,
        ]);
        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'status' => 'pending']);
    }

    public function test_returns_zero_progress_for_a_task_without_items(): void
    {
        $subject = Subject::query()->create([
            'name' => 'Historia',
            'credits' => 2,
        ]);
        $task = Task::query()->create([
            'subject_id' => $subject->id,
            'title' => 'Definir tema',
            'due_date' => '2026-10-03 10:00:00',
            'status' => 'pending',
        ]);

        $this->getJson("/api/subjects/{$subject->id}/tasks")
            ->assertOk()
            ->assertJsonPath('0.id', $task->id)
            ->assertJsonPath('0.progress', 0);
    }

    public function test_returns_422_when_an_item_description_is_missing(): void
    {
        $subject = Subject::query()->create([
            'name' => 'Biología',
            'credits' => 3,
        ]);
        $task = Task::query()->create([
            'subject_id' => $subject->id,
            'title' => 'Estudiar célula',
            'due_date' => '2026-10-03 10:00:00',
            'status' => 'pending',
        ]);

        $this->postJson("/api/tasks/{$task->id}/items", [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['description']);

        $this->assertDatabaseCount('task_items', 0);
    }
}

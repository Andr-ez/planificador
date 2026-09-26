<?php

namespace Tests\Feature;

use App\Models\Subject;
use App\Models\Task;
use App\Models\TaskItem;
use App\Models\User;
use App\Notifications\InactivityWarningNotification;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CheckStudyInactivityTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_sends_a_warning_for_a_pending_task_without_recent_activity(): void
    {
        $this->travelTo('2026-09-21 12:00:00');

        $user = User::factory()->create();
        $subject = Subject::query()->create(['name' => 'Matemáticas', 'credits' => 3]);
        $task = Task::query()->create([
            'subject_id' => $subject->id,
            'title' => 'Repasar álgebra',
            'due_date' => '2026-09-26 12:00:00',
            'status' => 'pending',
        ]);
        TaskItem::query()->create([
            'task_id' => $task->id,
            'description' => 'Resolver ejercicios',
            'is_completed' => false,
        ])->forceFill(['updated_at' => '2026-09-18 12:00:00'])->saveQuietly();
        $task->forceFill(['updated_at' => '2026-09-18 12:00:00'])->saveQuietly();

        Notification::fake();

        $this->artisan('study:check-inactivity')
            ->expectsOutput("Alerta: La tarea 'Repasar álgebra' de la materia 'Matemáticas' no ha tenido avances en 3 días. Quedan 5 días para la fecha límite.")
            ->assertSuccessful();

        Notification::assertSentToOnce($user, InactivityWarningNotification::class);
        Notification::assertSentTo($user, InactivityWarningNotification::class, function (InactivityWarningNotification $notification, array $channels): bool {
            return $notification->taskTitle === 'Repasar álgebra'
                && $notification->subjectName === 'Matemáticas'
                && $notification->daysRemaining === 5
                && $channels === ['mail'];
        });
    }

    public function test_does_not_send_a_warning_when_the_task_or_an_item_has_recent_activity(): void
    {
        $this->travelTo('2026-09-21 12:00:00');

        User::factory()->create();
        $subject = Subject::query()->create(['name' => 'Física', 'credits' => 4]);
        $task = Task::query()->create([
            'subject_id' => $subject->id,
            'title' => 'Preparar examen',
            'due_date' => '2026-09-26 12:00:00',
            'status' => 'pending',
        ]);
        TaskItem::query()->create([
            'task_id' => $task->id,
            'description' => 'Repasar teoría',
            'is_completed' => false,
        ]);
        $task->forceFill(['updated_at' => '2026-09-18 12:00:00'])->saveQuietly();

        Notification::fake();

        $this->artisan('study:check-inactivity')
            ->expectsOutput('No se encontraron tareas inactivas pendientes.')
            ->assertSuccessful();

        Notification::assertNothingSent();
    }

    public function test_builds_the_specified_mail_message(): void
    {
        $message = (new InactivityWarningNotification('Repasar álgebra', 'Matemáticas', 5))
            ->toMail(User::factory()->make());

        $this->assertInstanceOf(MailMessage::class, $message);
        $this->assertSame([
            "La tarea 'Repasar álgebra' de la materia 'Matemáticas' no ha tenido avances en 3 días. Quedan 5 días para la fecha límite.",
        ], $message->introLines);
    }
}

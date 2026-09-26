<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Models\User;
use App\Notifications\InactivityWarningNotification;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

#[Signature('study:check-inactivity')]
#[Description('Send inactivity warnings for pending study tasks')]
class CheckStudyInactivity extends Command
{
    public function handle(): int
    {
        $cutoff = now()->subDays(3);
        $foundInactiveTasks = false;

        Task::query()
            ->with('subject')
            ->where('status', 'pending')
            ->where('due_date', '>', now())
            ->where('updated_at', '<=', $cutoff)
            ->whereDoesntHave(
                'taskItems',
                fn (Builder $query): Builder => $query->where('updated_at', '>', $cutoff),
            )
            ->lazyById()
            ->each(function (Task $task) use (&$foundInactiveTasks): void {
                $foundInactiveTasks = true;
                $daysRemaining = (int) now()->diffInDays($task->due_date);
                $message = "Alerta: La tarea '{$task->title}' de la materia '{$task->subject->name}' no ha tenido avances en 3 días. Quedan {$daysRemaining} días para la fecha límite.";

                $this->warn($message);

                User::query()
                    ->lazyById()
                    ->each(function (User $user) use ($task, $daysRemaining): void {
                        $user->notify(new InactivityWarningNotification(
                            $task->title,
                            $task->subject->name,
                            $daysRemaining,
                        ));
                    });
            });

        if (! $foundInactiveTasks) {
            $this->info('No se encontraron tareas inactivas pendientes.');
        }

        return self::SUCCESS;
    }
}

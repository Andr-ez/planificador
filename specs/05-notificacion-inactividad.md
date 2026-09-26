# Spec 05: Notificación por Inactividad en Tareas

## Objetivo
Detectar tareas pendientes que tengan más de 3 días sin registrar acciones ("chuleos") en sus `task_items` y enviar una alerta calculando el tiempo restante para su fecha límite.

## Requisitos de Backend

1. **Clase de Notificación (`app/Notifications/InactivityWarningNotification.php`):**
   - Extiende de `Illuminate\Notifications\Notification`.
   - Mensaje de la alerta: 
     `"La tarea '[Título]' de la materia '[Materia]' no ha tenido avances en 3 días. Quedan [N] días para la fecha límite."`

2. **Comando Artisan (`app/Console/Commands/CheckStudyInactivity.php`):**
   - Signature: `study:check-inactivity`.
   - **Criterios de consulta Eloquent:**
     - Tareas con `status = 'pending'`.
     - Tareas cuya fecha límite (`due_date`) sea futura (es decir, materias/tareas aún activas).
     - Tareas cuyo `task_items` más reciente o la tarea misma no hayan registrado actualización en los últimos 3 días (`updated_at <= now()->subDays(3)`).
   - **Ejecución:** Dispara `InactivityWarningNotification` para las tareas que cumplan todos los criterios.
# Spec 07: Progreso de tareas por ítems

## Objetivo

Representar el avance accionable en las tareas y no en las materias. Una
materia agrupa tareas, créditos y horas de estudio; una tarea representa un
entregable con fecha límite y una lista de pasos (`task_items`).

## Reglas de dominio

- `Subject` no tiene barra ni porcentaje de progreso.
- `Subject` conserva `credits`, `total_hours_required`,
  `weekly_hours_required` y sus `study_sessions` para planificación.
- `Task` conserva `due_date` y expone `progress`.
- `Task.progress` es el porcentaje de `task_items` con `is_completed = true`
  sobre el total de ítems.
- Una tarea sin ítems tiene progreso `0%`.
- Una tarea está `completed` únicamente cuando tiene al menos un ítem y todos
  sus ítems están completos.
- Si existe algún ítem incompleto, la tarea está `pending`.

## Criterios de aceptación

1. El dashboard de materias no muestra barra ni porcentaje de progreso.
2. El detalle de una materia muestra la barra de progreso en cada tarea.
3. Marcar el último ítem como completo muestra `100%` y estado `completed`.
4. Desmarcar cualquier ítem muestra un porcentaje inferior a `100%` y estado
   `pending`.
5. Crear o agregar un ítem incompleto recalcula la tarea como `pending`.
6. La fecha límite de la tarea continúa siendo `tasks.due_date`.

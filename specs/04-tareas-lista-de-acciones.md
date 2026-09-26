# Spec 04: Tareas y Checklist de Acciones

## Objetivo
Permitir la creación de tareas asociadas a una materia, donde cada tarea contiene una lista de acciones ("items") que se pueden chulear para registrar progreso.

## Modelo de Datos (MySQL)

1. Tabla `task_items`
   - `id`: PK, auto_increment
   - `task_id`: FK -> tasks.id (cascadeOnDelete)
   - `description`: string (ej: "Leer capítulo 1")
   - `is_completed`: boolean (default: false)
   - `timestamps`: created_at, updated_at

## Requisitos de Backend

1. **Modelos (`app/Models/`):**
   - `TaskItem.php`: Define `$fillable = ['task_id', 'description', 'is_completed']` y relación `belongsTo(Task::class)`.
   - `Task.php`: Agrega la relación `hasMany(TaskItem::class)`.

2. **Controlador y Rutas (`TaskController.php`):**
   - `POST /api/subjects/{subject}/tasks`: Crea una tarea con su lista inicial de `task_items`.
   - `PATCH /api/task-items/{taskItem}/toggle`: Alterna el valor de `is_completed` (`true` <-> `false`).

3. **Regla de Estado Automático:**
   - Si el 100% de los `task_items` asociados a una `Task` pasan a `is_completed = true`, el `status` de la tarea cambia a `'completed'`.
   - Si se desmarca algún item, el `status` de la tarea vuelve a `'pending'`.
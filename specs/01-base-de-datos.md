# Spec 01: Estructura de Base de Datos (MySQL)

## Objetivo
Definir las tablas principales para registrar materias, tareas y sesiones de estudio.

## Tablas y Campos

1. `subjects` (Materias)
   - `id`: PK, auto_increment
   - `name`: string (Nombre de la materia)
   - `credits`: integer (Cantidad de créditos, min: 1)
   - `total_hours_required`: integer (Créditos * 48)
   - `weekly_hours_required`: decimal(5,2) (Horas totales / 12)
   - `timestamps`: created_at, updated_at

2. `tasks` (Tareas)
   - `id`: PK, auto_increment
   - `subject_id`: FK hacia `subjects.id` (con eliminación en cascada)
   - `title`: string
   - `due_date`: dateTime (Fecha y hora límite)
   - `status`: enum ('pending', 'in_progress', 'completed')
   - `timestamps`: created_at, updated_at

3. `study_sessions` (Sesiones de Estudio)
   - `id`: PK, auto_increment
   - `subject_id`: FK hacia `subjects.id` (con eliminación en cascada)
   - `duration_minutes`: integer (Duración acumulada)
   - `session_date`: dateTime
   - `timestamps`: created_at, updated_at
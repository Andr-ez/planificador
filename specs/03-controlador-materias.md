# Spec 03: Controlador y Validación de Materias

## Objetivo
Exponer endpoints API para registrar y consultar materias utilizando el servicio de planificación.

## Requisitos
1. Form Request: `app/Http/Requests/StoreSubjectRequest.php`
   - `name`: requerido, string, máximo 255 caracteres.
   - `credits`: requerido, entero, mínimo 1, máximo 10.
   - `weeks`: opcional, entero, mínimo 1, máximo 24.

2. Controlador: `app/Http/Controllers/SubjectController.php`
   - `index()`: Retorna todas las materias con sus horas calculadas y el progreso de estudio actual.
   - `store(StoreSubjectRequest $request)`:
     - Usa `StudyPlannerService` para calcular las horas según los créditos/semanas ingresados.
     - Guarda la materia en la tabla `subjects` de MySQL.
     - Retorna la materia creada con código HTTP 201 (Created).

3. Rutas: `routes/api.php`
   - `GET /api/subjects` -> `SubjectController@index`
   - `POST /api/subjects` -> `SubjectController@store`
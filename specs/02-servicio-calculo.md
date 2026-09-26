# Spec 02: Servicio de Planificación de Estudio

## Objetivo
Crear una clase de servicio que maneje la lógica de cálculo de horas por crédito y el cálculo de progreso de estudio.

## Requisitos
1. Clase: `app/Services/StudyPlannerService.php`.
2. Método `calculateHours(int $credits)`:
   - Recibe la cantidad de créditos.
   - Retorna un array con `total_hours` ($credits * 48) y `weekly_hours` ($total_hours / 12).
3. Método `calculateProgress(Subject $subject)`:
   - Suma el total de `duration_minutes` de las `study_sessions` asociadas a la materia.
   - Convierte los minutos a horas.
   - Retorna el porcentaje de avance respecto a `total_hours_required`.
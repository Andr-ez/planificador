# Spec 06: Interfaz Gráfica (Dashboard de Estudio)

## Objetivo
Crear las vistas de la aplicación para que el usuario gestione sus materias, tareas e ítems de forma visual conectándose a los endpoints API desarrollados en las Specs 01-05.

## Requisitos de UI / UX
1. **Vista Principal (Dashboard / Materias):**
   - Tarjetas por cada materia (`Subject`) mostrando su nombre, créditos y horas de estudio calculadas.
   - No mostrar barra ni porcentaje de progreso en `Subject`; el avance de entregables pertenece a sus tareas.
   - Formulario/Modal para crear una nueva materia[cite: 8].

2. **Vista de Detalle de Materia / Tareas:**
   - Lista de tareas (`Tasks`) pertenecientes a la materia con su estado (`pending` / `completed`) y fecha límite (`due_date`)[cite: 8].
   - Cada tarea debe mostrar una barra y porcentaje calculados como `task_items` completados sobre `task_items` totales.
   - Checklist jerárquico de subtareas (`task_items`) con casillas interactivas (checkbox) para conmutar (`toggle`) el estado[cite: 8].
   - Alerta visual destacada en color amarillo/rojo si la tarea es detectada como inactiva (sin cambios en más de 3 días)[cite: 8].

3. **Tecnología Frontend Recomendada:**
   - Vistas Laravel Blade + Tailwind CSS (o Alpine.js / Livewire para reactividad sin salir de Laravel)[cite: 7].
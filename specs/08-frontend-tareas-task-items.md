# Spec 08: Frontend de tareas y task items

## Objetivo

Permitir que el usuario navegue desde una tarjeta de `Subject` hacia su detalle
y gestione visualmente la jerarquía:

`Subject -> Task -> TaskItem`

## Navegación

- El botón “Ver tareas” de la tarjeta de una materia abre
  `/subjects/{subject}`.
- La ruta debe resolver el `Subject` mediante route model binding.
- Un subject inexistente debe devolver `404` y no una excepción de tipo.

## Vista de la materia

- Mostrar el nombre, créditos y horas objetivo de la materia.
- Mostrar un botón global “Nueva tarea”.
- Mostrar cada tarea con título, `due_date`, estado y progreso.
- Las tareas deben aparecer colapsadas inicialmente.
- Una flecha controla de forma independiente la expansión de cada tarea.
- Al expandir una tarea se muestran sus `task_items` y el formulario “Añadir
  task_item”.

## Task items

- Cada `task_item` se muestra con un checkbox.
- Marcar el checkbox llama a
  `PATCH /api/task-items/{taskItem}/toggle`.
- Desmarcarlo vuelve a llamar al mismo endpoint y revierte el estado.
- El porcentaje, la barra y el estado de la tarea deben actualizarse después del
  cambio sin recargar toda la página.
- Añadir un ítem llama a `POST /api/tasks/{task}/items` y actualiza únicamente
  la tarea correspondiente.

## Criterios de aceptación

1. “Ver tareas” abre correctamente el detalle de la materia.
2. El detalle carga las tasks asociadas a la materia y sus `task_items`.
3. Las tareas empiezan cerradas y pueden expandirse de manera independiente.
4. El usuario puede crear una nueva task desde el botón global.
5. El usuario puede crear un `task_item` dentro de la task expandida.
6. El checkbox permite completar y descompletar un `task_item`.
7. El progreso y `status` de la task reflejan cada cambio.
8. Los errores de API se muestran sin perder la tarea expandida.

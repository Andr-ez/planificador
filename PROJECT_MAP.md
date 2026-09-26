# MAPA DEL PROYECTO: Planificador de Estudio Universitario

## Stack Tecnológico
- Lenguaje: PHP 8.5.10
- Framework: Laravel 11
- Base de Datos: MySQL
- ORM: Eloquent

## Reglas de Arquitectura y Aislamiento
- Capa de Datos: Tablas y migraciones viven en `database/migrations/`. Modelos en `app/Models/`.
- Capa de Negocio: Lógica de cálculo de horas y créditos vive en Servicios (`app/Services/`).
- Capa de Validación: Reglas de entrada en Form Requests (`app/Http/Requests/`).
- Capa de Exposición: Controladores delgados en `app/Http/Controllers/`.
- Rutas: Definidas en `routes/api.php` o `routes/web.php`.
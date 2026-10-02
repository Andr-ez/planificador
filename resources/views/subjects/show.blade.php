@extends('layouts.app')

@section('title', 'Materia | Planificador')

@section('content')
<div id="subject-detail" data-subject-detail data-subject-id="{{ $subject->id }}">
    <a href="{{ url('/') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-700">&larr; Volver al dashboard</a>
    <div class="mt-5 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-medium text-indigo-600">Materia</p>
            <h1 data-subject-name class="mt-1 text-3xl font-semibold tracking-tight">Cargando...</h1>
            <p data-subject-meta class="mt-2 text-slate-600"></p>
        </div>
        <button type="button" data-open-task-form class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">Nueva tarea</button>
    </div>
    <div data-task-message class="mt-6 hidden rounded-lg border p-4 text-sm" role="alert"></div>
    <div data-tasks-loading class="mt-8 h-40 animate-pulse rounded-xl border border-slate-200 bg-white"></div>
    <div data-tasks-empty class="mt-8 hidden rounded-xl border border-dashed border-slate-300 bg-white p-10 text-center">
        <h2 class="font-semibold">No hay tareas para esta materia</h2>
        <p class="mt-2 text-sm text-slate-500">Añade una tarea para dividir tu trabajo en pasos.</p>
    </div>
    <div data-tasks-list class="mt-8 space-y-5"></div>
    <div data-task-form-panel class="mt-8 hidden rounded-xl border border-slate-200 bg-white p-6">
        <h2 class="text-xl font-semibold">Nueva tarea</h2>
        <form data-task-form class="mt-5 space-y-4">
            <div data-task-form-errors class="hidden rounded-lg bg-red-50 p-3 text-sm text-red-700"></div>
            <label class="block text-sm font-medium">Título
                <input name="title" required maxlength="255" class="mt-1 w-full rounded-lg border-slate-300 px-3 py-2">
            </label>
            <label class="block text-sm font-medium">Fecha límite
                <input name="due_date" type="datetime-local" required class="mt-1 w-full rounded-lg border-slate-300 px-3 py-2">
            </label>
            <div>
                <div class="flex items-center justify-between">
                    <label class="text-sm font-medium">Ítems</label>
                    <button type="button" data-add-task-item class="text-sm font-medium text-indigo-600">Añadir ítem</button>
                </div>
                <div data-task-items class="mt-2 space-y-2"></div>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" data-close-task-form class="rounded-lg px-4 py-2 text-sm text-slate-600 hover:bg-slate-100">Cancelar</button>
                <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 disabled:opacity-50">Crear tarea</button>
            </div>
        </form>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Dashboard | Planificador')

@section('content')
<div id="dashboard" data-dashboard>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-medium text-indigo-600">Resumen académico</p>
            <h1 class="mt-1 text-3xl font-semibold tracking-tight">Mis materias</h1>
            <p class="mt-2 text-slate-600">Organiza tus objetivos y sigue tu progreso de estudio.</p>
        </div>
        <button type="button" data-open-subject-modal class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
            Nueva materia
        </button>
    </div>

    <div data-dashboard-message class="mt-6 hidden rounded-lg border p-4 text-sm" role="alert"></div>
    <div data-subjects-loading class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        @for ($i = 0; $i < 3; $i++)
            <div class="h-48 animate-pulse rounded-xl border border-slate-200 bg-white"></div>
        @endfor
    </div>
    <div data-subjects-empty class="mt-8 hidden rounded-xl border border-dashed border-slate-300 bg-white p-10 text-center">
        <h2 class="font-semibold">Aún no tienes materias</h2>
        <p class="mt-2 text-sm text-slate-500">Crea tu primera materia para empezar a planificar.</p>
    </div>
    <div data-subjects-grid class="mt-8 hidden grid gap-5 sm:grid-cols-2 lg:grid-cols-3"></div>

    <div data-subject-modal class="fixed inset-0 z-10 hidden items-center justify-center bg-slate-900/50 p-4" role="dialog" aria-modal="true" aria-labelledby="subject-modal-title">
        <form data-subject-form class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
            <div class="flex items-start justify-between">
                <div>
                    <h2 id="subject-modal-title" class="text-xl font-semibold">Nueva materia</h2>
                    <p class="mt-1 text-sm text-slate-500">Define la materia y sus créditos.</p>
                </div>
                <button type="button" data-close-subject-modal class="text-2xl leading-none text-slate-400 hover:text-slate-600" aria-label="Cerrar">&times;</button>
            </div>
            <div data-form-errors class="mt-4 hidden rounded-lg bg-red-50 p-3 text-sm text-red-700"></div>
            <label class="mt-5 block text-sm font-medium text-slate-700">Nombre
                <input name="name" required maxlength="255" class="mt-1 w-full rounded-lg border-slate-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </label>
            <label class="mt-4 block text-sm font-medium text-slate-700">Créditos
                <input name="credits" type="number" min="1" max="10" required class="mt-1 w-full rounded-lg border-slate-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </label>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" data-close-subject-modal class="rounded-lg px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100">Cancelar</button>
                <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 disabled:opacity-50">Guardar</button>
            </div>
        </form>
    </div>
</div>
@endsection

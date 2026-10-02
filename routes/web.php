<?php

use App\Models\Subject;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('dashboard');
});

Route::get('/subjects/{subject}', function (Subject $subject) {
    return view('subjects.show', compact('subject'));
})->name('subjects.show');

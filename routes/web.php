<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('dashboard');
});

Route::get('/subjects/{subject}', function (int $subject) {
    return view('subjects.show', compact('subject'));
})->name('subjects.show');

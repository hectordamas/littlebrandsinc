<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{StudentsController};

Route::get('/', function () {
    return redirect('login');
});


Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');


Route::get('inscripciones-y-clientes', function () {
    return redirect('students');
});

Route::get('inscripcion', function () { return view('auth.student-form'); });
Route::post('/registro-estudiante', [StudentsController::class, 'register'])->name('students.register');

Route::middleware(['auth'])->group(function () {
    Route::get('inscripciones-y-clientes', function () {
        return redirect('students');
    });

    Route::get('students', [StudentsController::class, 'index']);


    Route::get('finanzas-y-facturacion');
    Route::get('programacion-y-operaciones');
});

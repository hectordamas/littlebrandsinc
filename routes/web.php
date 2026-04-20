<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{StudentsController, EnrollmentController, UsersController, CoursesController, BranchesController};
Route::get('/', function () {
    return redirect('login');
});


Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');


Route::get('inscripciones-y-clientes', function () {
    return redirect('students');
});

Route::get('inscripcion', function () { return view('auth.inscripcion-form'); });
Route::post('/registro-estudiante', [StudentsController::class, 'register'])->name('students.register');

Route::middleware(['auth'])->group(function () {
    Route::get('enrollment', [EnrollmentController::class, 'index']);

    Route::get('students', [StudentsController::class, 'index']);


    Route::get('finanzas-y-facturacion');
    Route::get('programacion-y-operaciones');


    Route::get('users', [UsersController::class, 'index'])->name('users.index');
    Route::get('users/{id}/edit', [UsersController::class, 'edit'])->name('users.edit');
    Route::put('users/{id}', [UsersController::class, 'update'])->name('users.update');
    Route::delete('users/{id}', [UsersController::class, 'destroy'])->name('users.destroy');
    Route::post('users', [UsersController::class, 'store'])->name('users.store');

    Route::get('courses', [CoursesController::class, 'index'])->name('courses.index');
    Route::get('courses/create', [CoursesController::class, 'create'])->name('courses.create');
    Route::post('courses', [CoursesController::class, 'store'])->name('courses.store');
    Route::get('courses/{id}/edit', [CoursesController::class, 'edit'])->name('courses.edit');
    Route::put('courses/{id}', [CoursesController::class, 'update'])->name('courses.update');         
    
    Route::get('branches', [BranchesController::class, 'index'])->name('branches.index');
    Route::get('branches/create', [BranchesController::class, 'create'])->name('branches.create');
    Route::post('branches', [BranchesController::class, 'store'])->name('branches.store');
    Route::get('branches/{id}/edit', [BranchesController::class, 'edit'])->name('branches.edit');
    Route::put('branches/{id}', [BranchesController::class, 'update'])->name('branches.update');    
    Route::delete('branches/{id}', [BranchesController::class, 'destroy'])->name('branches.destroy');
});

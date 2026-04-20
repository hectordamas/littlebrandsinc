<?php

use App\Http\Controllers\{BranchesController, CoursesController, EnrollmentController, EnrollmentWizardController, HomeController, StudentsController, UsersController};
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('login');
});

Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');

Route::get('inscripciones-y-clientes', function () {
    return redirect('students');
});

Route::get('inscripcion', function () {
    return view('auth.inscripcion-form');
});
Route::post('/registro-estudiante', [StudentsController::class, 'register'])->name('students.register');

Route::get('inscripcion/wizard', [EnrollmentWizardController::class, 'show'])->name('enrollment.wizard');
Route::post('inscripcion/wizard', [EnrollmentWizardController::class, 'submit'])->name('enrollment.wizard.submit');
Route::get('inscripcion/wizard/reset', [EnrollmentWizardController::class, 'reset'])->name('enrollment.wizard.reset');


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
    Route::delete('courses/{id}', [CoursesController::class, 'destroy'])->name('courses.destroy');

    Route::get('branches', [BranchesController::class, 'index'])->name('branches.index');
    Route::get('branches/create', [BranchesController::class, 'create'])->name('branches.create');
    Route::post('branches', [BranchesController::class, 'store'])->name('branches.store');
    Route::get('branches/{id}/edit', [BranchesController::class, 'edit'])->name('branches.edit');
    Route::put('branches/{id}', [BranchesController::class, 'update'])->name('branches.update');
    Route::delete('branches/{id}', [BranchesController::class, 'destroy'])->name('branches.destroy');

    Route::delete('classes/{id}', [CoursesController::class, 'destroyClass'])->name('courses.classes.destroy');
    Route::put('classes/{id}', [CoursesController::class, 'updateClass'])->name('courses.classes.update');
    Route::post('classes', [CoursesController::class, 'storeClass'])->name('courses.classes.store');
});

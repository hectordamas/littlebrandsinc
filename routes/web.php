<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('login');
});


Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');


Route::get('inscripciones-y-clientes');
Route::get('finanzas-y-facturacion');
Route::get('programacion-y-operaciones');

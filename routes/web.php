<?php

use App\Http\Controllers\{AccountsController, BranchesController, CoachPortalController, CoursesController, EnrollmentController, EnrollmentWizardController, FinanceController, HomeController, LandingController, ParentPortalController, StripeWebhookController, StudentsController, UsersController};
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;

Route::get('/', [LandingController::class, 'index'])->name('landing.index');
Route::post('/contacto', [LandingController::class, 'contact'])->name('landing.contact');

Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');

Route::get('inscripciones-y-clientes', function () {
    return redirect('students');
});

Route::get('inscripcion', function () {
    return redirect()->route('enrollment.wizard');
});
Route::post('/registro-estudiante', [StudentsController::class, 'register'])->name('students.register');

Route::get('inscripcion/wizard', [EnrollmentWizardController::class, 'show'])->name('enrollment.wizard');
Route::post('inscripcion/wizard', [EnrollmentWizardController::class, 'submit'])->name('enrollment.wizard.submit');
Route::post('inscripcion/wizard/payment-intent', [EnrollmentWizardController::class, 'createPaymentIntent'])->name('enrollment.wizard.payment-intent');
Route::get('inscripcion/wizard/reset', [EnrollmentWizardController::class, 'reset'])->name('enrollment.wizard.reset');
Route::post('stripe/webhook', StripeWebhookController::class)
    ->withoutMiddleware([ValidateCsrfToken::class])
    ->name('stripe.webhook');

Route::middleware(['auth'])->group(function () {
    Route::get('profile', [UsersController::class, 'profile'])->name('users.profile');
    Route::put('profile', [UsersController::class, 'updateProfile'])->name('users.profile.update');
});


Route::middleware(['auth', 'role:Administrador'])->group(function () {
    Route::get('enrollment', [EnrollmentController::class, 'index']);
    Route::post('enrollment/store', [EnrollmentController::class, 'store'])->name('enrollment.store');
    Route::patch('enrollment/{enrollment}/status', [EnrollmentController::class, 'updateStatus'])->name('enrollment.status');
    Route::patch('enrollment/bulk-update', [EnrollmentController::class, 'bulkUpdate'])->name('enrollment.bulk-update');
    Route::get('enrollment/{enrollment}', [EnrollmentController::class, 'show'])->name('enrollment.show');
    Route::get('enrollment/{enrollment}/receipt', [EnrollmentController::class, 'downloadReceipt'])->name('enrollment.receipt');
    Route::patch('enrollment/{enrollment}', [EnrollmentController::class, 'update'])->name('enrollment.update');

    Route::get('students', [StudentsController::class, 'index'])->name('students.index');
    Route::get('students/{student}', [StudentsController::class, 'show'])->name('students.show');
    Route::get('parents', [UsersController::class, 'parents'])->name('parents.index');
    Route::get('trainers', [UsersController::class, 'trainers'])->name('trainers.index');

    Route::get('finanzas-y-facturacion', [FinanceController::class, 'index'])->name('finance.index');
    Route::get('finanzas-y-facturacion/cobranzas', [FinanceController::class, 'collections'])->name('finance.collections');
    Route::post('finanzas-y-facturacion/cobranzas', [FinanceController::class, 'storeCollection'])->name('finance.collections.store');
    Route::get('finanzas-y-facturacion/cobranzas/{receivable}', [FinanceController::class, 'showCollection'])->name('finance.collections.show');
    Route::post('finanzas-y-facturacion/cobranzas/{receivable}/abonos', [FinanceController::class, 'storeCollectionPayment'])->name('finance.collections.payments.store');
    Route::get('finanzas-y-facturacion/cuentas-por-pagar', [FinanceController::class, 'payables'])->name('finance.payables');
    Route::post('finanzas-y-facturacion/cuentas-por-pagar', [FinanceController::class, 'storePayable'])->name('finance.payables.store');
    Route::get('finanzas-y-facturacion/cuentas-por-pagar/{payable}', [FinanceController::class, 'showPayable'])->name('finance.payables.show');
    Route::post('finanzas-y-facturacion/cuentas-por-pagar/{payable}/abonos', [FinanceController::class, 'storePayablePayment'])->name('finance.payables.payments.store');
    Route::post('finanzas-y-facturacion/movimientos', [FinanceController::class, 'storeTransaction'])->name('finance.transactions.store');
    Route::get('finanzas-y-facturacion/movimientos/{transaction}/comprobante', [FinanceController::class, 'downloadTransactionReceipt'])->name('finance.transactions.receipt');

    Route::get('accounts', [AccountsController::class, 'index'])->name('accounts.index');
    Route::post('accounts', [AccountsController::class, 'store'])->name('accounts.store');
    Route::get('accounts/{id}/edit', [AccountsController::class, 'edit'])->name('accounts.edit');
    Route::put('accounts/{id}', [AccountsController::class, 'update'])->name('accounts.update');
    
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

    Route::get('calendar', [CoursesController::class, 'calendar'])->name('calendar.index');
    Route::get('calendar/events', [CoursesController::class, 'calendarEvents'])->name('calendar.events');
});

Route::middleware(['auth', 'role:Padre'])->group(function () {
    Route::get('mi-panel', [ParentPortalController::class, 'index'])->name('parent.portal');
});

Route::middleware(['auth', 'role:Coach'])->group(function () {
    Route::get('coach/calendario', [CoachPortalController::class, 'calendar'])->name('coach.calendar');
    Route::get('coach/calendario/events', [CoachPortalController::class, 'events'])->name('coach.calendar.events');
    Route::post('coach/clases/{class}/attendance', [CoachPortalController::class, 'markAttendance'])->name('coach.classes.attendance');
});

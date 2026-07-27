<?php

use App\Http\Controllers\{AccountsController, BirthdayInquiryController, BranchesController, CoachPortalController, ContactMessageController, CoursesController, EnrollmentController, EnrollmentWizardController, FinanceController, HomeController, LandingController, ParentPortalController, ProgramsController, StripeWebhookController, StudentsController, UsersController, WaitlistController};
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;

// Rutas públicas para páginas legales
Route::view('/terms', 'terms-and-condition')->name('legal.terms');
Route::view('/privacy', 'privacy-policy')->name('legal.privacy');

// API ocupación de curso
Route::get('courses/{id}/occupancy', [CoursesController::class, 'occupancy'])->name('courses.occupancy');
Route::get('/', [LandingController::class, 'index'])->name('landing.index');
Route::get('/clases', [LandingController::class, 'classes'])->name('classes.index');
Route::post('/contacto', [LandingController::class, 'contact'])->name('landing.contact');
Route::post('/cumpleanos', [BirthdayInquiryController::class, 'store'])->name('birthdays.store');

Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');

Route::get('Inscripciones-y-clientes', function () {
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
    Route::get('enrollment/check-fee', [EnrollmentController::class, 'checkFee'])->name('enrollment.check-fee');
    Route::post('enrollment/store', [EnrollmentController::class, 'store'])->name('enrollment.store');
    Route::patch('enrollment/{enrollment}/status', [EnrollmentController::class, 'updateStatus'])->name('enrollment.status');
    Route::post('enrollment/{enrollment}/attach-payment', [EnrollmentController::class, 'attachPayment'])->name('enrollment.attach-payment');
    Route::patch('enrollment/bulk-update', [EnrollmentController::class, 'bulkUpdate'])->name('enrollment.bulk-update');
    Route::get('enrollment/{enrollment}', [EnrollmentController::class, 'show'])->name('enrollment.show');
    Route::get('enrollment/{enrollment}/receipt', [EnrollmentController::class, 'downloadReceipt'])->name('enrollment.receipt');
    Route::patch('enrollment/{enrollment}', [EnrollmentController::class, 'update'])->name('enrollment.update');

    Route::get('students', [StudentsController::class, 'index'])->name('students.index');
    Route::get('students/import', [StudentsController::class, 'importForm'])->name('students.import.form');
    Route::post('students/import', [StudentsController::class, 'importStore'])->name('students.import.store');
    Route::get('students/{student}', [StudentsController::class, 'show'])->name('students.show');
    Route::get('parents', [UsersController::class, 'parents'])->name('parents.index');
    Route::get('trainers', [UsersController::class, 'trainers'])->name('trainers.index');

    Route::get('finanzas-y-facturacion', [FinanceController::class, 'index'])->name('finance.index');
    Route::get('finanzas-y-facturacion/cobranzas', [FinanceController::class, 'collections'])->name('finance.collections');
    Route::post('finanzas-y-facturacion/cobranzas', [FinanceController::class, 'storeCollection'])->name('finance.collections.store');
    Route::get('finanzas-y-facturacion/cobranzas/{receivable}', [FinanceController::class, 'showCollection'])->name('finance.collections.show');
    Route::patch('finanzas-y-facturacion/cobranzas/{receivable}', [FinanceController::class, 'updateCollection'])->name('finance.collections.update');
    Route::post('finanzas-y-facturacion/cobranzas/{receivable}/abonos', [FinanceController::class, 'storeCollectionPayment'])->name('finance.collections.payments.store');
    Route::get('finanzas-y-facturacion/cuentas-por-pagar', [FinanceController::class, 'payables'])->name('finance.payables');
    Route::post('finanzas-y-facturacion/cuentas-por-pagar', [FinanceController::class, 'storePayable'])->name('finance.payables.store');
    Route::get('finanzas-y-facturacion/cuentas-por-pagar/{payable}', [FinanceController::class, 'showPayable'])->name('finance.payables.show');
    Route::post('finanzas-y-facturacion/cuentas-por-pagar/{payable}/abonos', [FinanceController::class, 'storePayablePayment'])->name('finance.payables.payments.store');
    Route::post('finanzas-y-facturacion/movimientos', [FinanceController::class, 'storeTransaction'])->name('finance.transactions.store');
    Route::put('finanzas-y-facturacion/movimientos/{transaction}', [FinanceController::class, 'updateTransaction'])->name('finance.transactions.update');
    Route::delete('finanzas-y-facturacion/movimientos/{transaction}', [FinanceController::class, 'destroyTransaction'])->name('finance.transactions.destroy');
    Route::get('finanzas-y-facturacion/movimientos/{transaction}/comprobante', [FinanceController::class, 'downloadTransactionReceipt'])->name('finance.transactions.receipt');
    Route::get('finanzas-y-facturacion/pagos-padres', [FinanceController::class, 'parentPayments'])->name('finance.parent-payments');
    Route::patch('finanzas-y-facturacion/pagos-padres/{payment}/approve', [FinanceController::class, 'approveParentPayment'])->name('finance.parent-payments.approve');
    Route::patch('finanzas-y-facturacion/pagos-padres/{payment}/reject', [FinanceController::class, 'rejectParentPayment'])->name('finance.parent-payments.reject');
    Route::get('mensajes', [ContactMessageController::class, 'index'])->name('messages.index');
    Route::patch('mensajes/{message}/read', [ContactMessageController::class, 'markAsRead'])->name('messages.read');
    Route::patch('mensajes/{message}/unread', [ContactMessageController::class, 'markAsUnread'])->name('messages.unread');

    Route::get('cumpleanos', [BirthdayInquiryController::class, 'index'])->name('birthdays.index');
    Route::patch('cumpleanos/{birthday}/read', [BirthdayInquiryController::class, 'markAsRead'])->name('birthdays.read');
    Route::patch('cumpleanos/{birthday}/unread', [BirthdayInquiryController::class, 'markAsUnread'])->name('birthdays.unread');

    Route::get('accounts', [AccountsController::class, 'index'])->name('accounts.index');
    Route::post('accounts', [AccountsController::class, 'store'])->name('accounts.store');
    Route::get('accounts/{id}/edit', [AccountsController::class, 'edit'])->name('accounts.edit');
    Route::put('accounts/{id}', [AccountsController::class, 'update'])->name('accounts.update');
    
    Route::get('programs', [ProgramsController::class, 'index'])->name('programs.index');
    Route::get('programs/{id}/edit', [ProgramsController::class, 'edit'])->name('programs.edit');
    Route::put('programs/{id}', [ProgramsController::class, 'update'])->name('programs.update');

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

    Route::get('lista-de-espera', [WaitlistController::class, 'index'])->name('waitlists.index');
    Route::patch('lista-de-espera/{waitlist}/approve', [WaitlistController::class, 'approve'])->name('waitlists.approve');
    Route::patch('lista-de-espera/{waitlist}/reject', [WaitlistController::class, 'reject'])->name('waitlists.reject');
});

Route::middleware(['auth', 'role:Padre'])->group(function () {
    Route::get('mi-panel', [ParentPortalController::class, 'index'])->name('parent.portal');
    
    // Clases y asistencias
    Route::get('mi-panel/clases-y-asistencias', [ParentPortalController::class, 'calendar'])->name('parent.calendar');
    Route::get('mi-panel/clases-y-asistencias/events', [ParentPortalController::class, 'calendarEvents'])->name('parent.calendar.events');
    
    // Pagos
    Route::get('mi-panel/pagos', [ParentPortalController::class, 'payments'])->name('parent.payments');
    Route::post('mi-panel/pagos', [ParentPortalController::class, 'registerPayment'])->name('parent.payments.store');
});

Route::middleware(['auth', 'role:Coach'])->group(function () {
    Route::get('coach/calendario', [CoachPortalController::class, 'calendar'])->name('coach.calendar');
    Route::get('coach/calendario/events', [CoachPortalController::class, 'events'])->name('coach.calendar.events');
    Route::post('coach/clases/{class}/attendance', [CoachPortalController::class, 'markAttendance'])->name('coach.classes.attendance');
});

// Vista previa de correos en entorno local
if (config('app.env') === 'local') {
    Route::get('preview-mail/approved', function () {
        $payment = \App\Models\ParentPayment::with(['user', 'receivable'])->first();
        if (!$payment) {
            $user = \App\Models\User::first() ?? new \App\Models\User(['name' => 'Héctor Damas']);
            $receivable = \App\Models\AccountReceivable::first() ?? new \App\Models\AccountReceivable(['title' => 'Inscripción #1 - Little Strikers']);
            $payment = new \App\Models\ParentPayment([
                'amount' => 90.00,
                'reference' => 'REF-94829',
                'status' => 'approved',
                'approved_at' => now(),
            ]);
            $payment->setRelation('user', $user);
            $payment->setRelation('receivable', $receivable);
        }
        return new \App\Mail\PaymentApproved($payment);
    });

    Route::get('preview-mail/rejected', function () {
        $payment = \App\Models\ParentPayment::with(['user', 'receivable'])->first();
        if (!$payment) {
            $user = \App\Models\User::first() ?? new \App\Models\User(['name' => 'Héctor Damas']);
            $receivable = \App\Models\AccountReceivable::first() ?? new \App\Models\AccountReceivable(['title' => 'Inscripción #1 - Little Strikers']);
            $payment = new \App\Models\ParentPayment([
                'amount' => 90.00,
                'reference' => 'REF-94829',
                'status' => 'rejected',
                'rejected_reason' => 'El comprobante subido está borroso o no muestra el número de transacción bancaria.',
            ]);
            $payment->setRelation('user', $user);
            $payment->setRelation('receivable', $receivable);
        } else {
            $payment->rejected_reason = $payment->rejected_reason ?? 'El comprobante subido está borroso o no muestra el número de transacción bancaria.';
        }
        return new \App\Mail\PaymentRejected($payment);
    });

    Route::get('preview-mail/landing-contact', function () {
        $payload = [
            'representative_name' => 'Héctor Damas',
            'child_name' => 'Pedrito Damas',
            'child_age' => 8,
            'program_name' => 'Little Strikers (Fútbol)',
            'branch_name' => 'Sede Las Mercedes',
            'email' => 'hector@example.com',
            'phone' => '+58 412 1234567',
            'comment' => "Hola, estoy interesado en inscribir a mi hijo en el horario de las tardes. ¿Tienen cupos disponibles?"
        ];
        return new \App\Mail\LandingContactMailable($payload);
    });

    Route::get('preview-mail/reminder', function () {
        $installment = \App\Models\EnrollmentInstallment::first();
        if (!$installment) {
            $enrollment = \App\Models\Enrollment::first() ?? new \App\Models\Enrollment();
            $installment = new \App\Models\EnrollmentInstallment([
                'amount' => 90.00,
                'due_date' => now()->addDays(3),
            ]);
            $installment->setRelation('enrollment', $enrollment);
        }
        $notification = new \App\Notifications\InstallmentDueReminderNotification($installment, 3);
        $user = \App\Models\User::first() ?? new \App\Models\User(['name' => 'Héctor Damas']);
        return $notification->toMail($user);
    });
}

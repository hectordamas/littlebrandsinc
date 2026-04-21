<?php

namespace App\Http\Controllers;

use App\Models\{AccountPayable, AccountReceivable, Branch, Course, Enrollment, LBClass, Student, Transaction, User};
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $studentsCount = (int) Student::query()->count();
        $enrollmentsCount = (int) Enrollment::query()->count();
        $activeCoursesCount = (int) Course::query()->where('active', true)->count();
        $coachesCount = (int) User::query()->where('role', 'Coach')->count();
        $branchesCount = (int) Branch::query()->count();

        $completedIncome = (float) Transaction::query()
            ->where('type', 'income')
            ->where('status', 'completed')
            ->sum('amount');

        $completedExpense = (float) Transaction::query()
            ->where('type', 'expense')
            ->where('status', 'completed')
            ->sum('amount');

        $pendingReceivables = (float) AccountReceivable::query()
            ->whereIn('status', ['pending', 'partial'])
            ->sum('balance_due');

        $pendingPayables = (float) AccountPayable::query()
            ->whereIn('status', ['pending', 'partial'])
            ->sum('balance_due');

        $todayClasses = (int) LBClass::query()
            ->whereDate('date', Carbon::today()->toDateString())
            ->count();

        $next7DaysClasses = (int) LBClass::query()
            ->whereBetween('date', [Carbon::today()->toDateString(), Carbon::today()->addDays(6)->toDateString()])
            ->count();

        $monthlyStart = Carbon::now()->startOfMonth()->subMonths(5);
        $monthlyRaw = Transaction::query()
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym")
            ->selectRaw("SUM(CASE WHEN type = 'income' AND status = 'completed' THEN amount ELSE 0 END) as income")
            ->selectRaw("SUM(CASE WHEN type = 'expense' AND status = 'completed' THEN amount ELSE 0 END) as expense")
            ->whereDate('created_at', '>=', $monthlyStart->toDateString())
            ->groupBy('ym')
            ->orderBy('ym')
            ->get()
            ->keyBy('ym');

        $monthlyLabels = [];
        $monthlyIncome = [];
        $monthlyExpense = [];

        for ($i = 0; $i < 6; $i++) {
            $month = Carbon::now()->startOfMonth()->subMonths(5 - $i);
            $key = $month->format('Y-m');
            $monthlyLabels[] = ucfirst($month->translatedFormat('M Y'));
            $monthlyIncome[] = (float) optional($monthlyRaw->get($key))->income;
            $monthlyExpense[] = (float) optional($monthlyRaw->get($key))->expense;
        }

        $paymentDistribution = Enrollment::query()
            ->select('payment_status', DB::raw('COUNT(*) as total'))
            ->groupBy('payment_status')
            ->pluck('total', 'payment_status');

        $enrollmentPaid = (int) ($paymentDistribution['paid'] ?? 0);
        $enrollmentPending = (int) ($paymentDistribution['pending'] ?? 0);

        $classesByBranchRaw = LBClass::query()
            ->join('branches', 'branches.id', '=', 'classes.branch_id')
            ->select('branches.name as branch_name', DB::raw('COUNT(classes.id) as classes_total'))
            ->groupBy('branches.id', 'branches.name')
            ->orderByDesc('classes_total')
            ->limit(6)
            ->get();

        $classesByBranchLabels = $classesByBranchRaw->pluck('branch_name')->values();
        $classesByBranchValues = $classesByBranchRaw->pluck('classes_total')->map(fn ($value) => (int) $value)->values();

        return view('home', [
            'studentsCount' => $studentsCount,
            'enrollmentsCount' => $enrollmentsCount,
            'activeCoursesCount' => $activeCoursesCount,
            'coachesCount' => $coachesCount,
            'branchesCount' => $branchesCount,
            'completedIncome' => $completedIncome,
            'completedExpense' => $completedExpense,
            'pendingReceivables' => $pendingReceivables,
            'pendingPayables' => $pendingPayables,
            'netBalance' => $completedIncome - $completedExpense,
            'todayClasses' => $todayClasses,
            'next7DaysClasses' => $next7DaysClasses,
            'monthlyLabels' => $monthlyLabels,
            'monthlyIncome' => $monthlyIncome,
            'monthlyExpense' => $monthlyExpense,
            'enrollmentPaid' => $enrollmentPaid,
            'enrollmentPending' => $enrollmentPending,
            'classesByBranchLabels' => $classesByBranchLabels,
            'classesByBranchValues' => $classesByBranchValues,
        ]);
    }
}

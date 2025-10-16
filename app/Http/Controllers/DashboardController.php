<?php

namespace App\Http\Controllers;

use App\Models\Dashboard;
use Illuminate\Http\Request;
use App\Models\Answer;
use App\Models\Question;
use App\Models\Diagnostic;
use App\Models\Campaign;
use App\Models\User;
use App\Models\Tenant;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $authUser = auth()->user();

        $pendingDiagnostics = collect();
        $pendingCount = 0;
        $pendingUsers = collect();
        $healthIndex = 0;
        $companiesHealth = collect();

        if ($authUser->role === 'superadmin') {
            $tenants = Tenant::with(['diagnostics.questions.answers'])->get();

            $companiesHealth = $tenants->map(function ($tenant) {
                $allAnswers = $tenant->diagnostics
                    ->flatMap(fn($diag) => $diag->questions
                        ->flatMap(fn($q) => $q->answers)
                        ->where('tenant_id', $tenant->id)
                    );
                $allScores = $allAnswers->map(fn($ans) => $ans->note ?? $ans->score ?? null)->filter();

                $overallAverage = $allScores->count() ? $allScores->avg() : 0;
                $healthIndex = round(($overallAverage / 5) * 100, 2);

                $pendingDiagnostics = $tenant->diagnostics->filter(function ($diag) use ($tenant) {
                    return !$diag->questions
                        ->flatMap(fn($q) => $q->answers)
                        ->where('tenant_id', $tenant->id)
                        ->count();
                });

                return [
                    'tenant' => $tenant,
                    'healthIndex' => $healthIndex,
                    'pendingDiagnostics' => $pendingDiagnostics
                ];
            });
        } else {
            $tenantId = $authUser->tenant_id;

            $diagnostics = Diagnostic::with(['questions.answers'])
                ->where('plain_id', $tenantId)
                ->get();

            $allAnswers = $diagnostics
                ->flatMap(fn($diag) => $diag->questions
                    ->flatMap(fn($q) => $q->answers)
                    ->where('tenant_id', $tenantId)
                );

            $allScores = $allAnswers->map(fn($ans) => $ans->note ?? $ans->score ?? null)
                ->filter(fn($score) => $score !== null);

            $overallAverage = $allScores->count() ? $allScores->avg() : 0;
            $healthIndex = round(($overallAverage / 5) * 100, 2);

            $pendingDiagnostics = $diagnostics->filter(fn($diag) =>
                !$diag->questions->flatMap(fn($q) => $q->answers)->where('user_id', $authUser->id)->count()
            );

            $pendingCount = $pendingDiagnostics->count();

            if ($authUser->role === 'admin') {
                $users = User::where('tenant_id', $tenantId)->get();
                foreach ($users as $user) {
                    $userPending = $diagnostics->filter(fn($diag) =>
                        !$diag->questions->flatMap(fn($q) => $q->answers)->where('user_id', $user->id)->count()
                    );

                    if ($userPending->isNotEmpty()) {
                        $pendingUsers->push([
                            'user' => $user,
                            'pendingCount' => $userPending->count(),
                            'pendingDiagnostics' => $userPending
                        ]);
                    }
                }
            }
        }

        return view('dashboard.index', [
            'authUser'           => $authUser,
            'pendingDiagnostics' => $pendingDiagnostics,
            'pendingCount'       => $pendingCount,
            'pendingUsers'       => $pendingUsers,
            'healthIndex'        => $healthIndex,
            'companiesHealth'    => $companiesHealth,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Dashboard $dashboard)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Dashboard $dashboard)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Dashboard $dashboard)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Dashboard $dashboard)
    {
        //
    }

    // public function notification() {
    //     $user = auth()->user();
        
    //     $diagnostics = Diagnostic::whereHas('periods', function($query) use ($user) {
    //         $query->where('tenant_id', $user->tenant_id)
    //             ->whereDate('start', '<=', now())
    //             ->whereDate('end', '>=', now());
    //     })->with(['periods' => function($query) use ($user) {
    //         $query->where('tenant_id', $user->tenant_id)
    //             ->whereDate('start', '<=', now())
    //             ->whereDate('end', '>=', now());
    //     }])->get();

    //     $diagnosticsNotAnswered = $diagnostics->filter(function($diagnostic) use ($user) {
    //         $period = $diagnostic->periods->first();

    //         if (!$period) return false;

    //         $hasTargetedQuestions = DB::table('diagnostic_question')
    //             ->join('questions', 'diagnostic_question.question_id', '=', 'questions.id')
    //             ->where('diagnostic_question.diagnostic_id', $diagnostic->id)
    //             ->whereJsonContains('diagnostic_question.target', $user->role)
    //             ->exists();

    //         if (!$hasTargetedQuestions) return false;

    //         $alreadyAnswered = $diagnostic->answers()
    //             ->where('user_id', $user->id)
    //             ->where('diagnostic_period_id', $period->id)
    //             ->exists();

    //         return !$alreadyAnswered;
    //     })->values();

    //     $notifications = $diagnosticsNotAnswered->map(function($diag) {
    //         $period = $diag->periods->first();
    //         if (!$period) return null;

    //         return [
    //             'id' => $diag->id,
    //             'title' => $diag->title,
    //             'deadline' => $period->end->toDateString(),
    //             'days_left' => now()->diffInDays($period->end, false),
    //         ];
    //     })->filter()->values();

    //     return view('dashboard', ['notifications' => $notifications ?? collect()]);
    // }
}

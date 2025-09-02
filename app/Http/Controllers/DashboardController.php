<?php

namespace App\Http\Controllers;

use App\Models\Dashboard;
use Illuminate\Http\Request;
use App\Models\Answer;
use App\Models\Question;
use App\Models\Diagnostic;
use App\Models\Campaign;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view ('dashboard.index');
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

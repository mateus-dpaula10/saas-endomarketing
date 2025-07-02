<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Diagnostic;
use Illuminate\Http\Request;
use App\Models\Answer;
use App\Models\Tenant;
use App\Models\DiagnosticPeriod;
use Illuminate\Support\Facades\Auth;

class DiagnosticController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $now = Carbon::now();
        $role = $user->role;
        $tenantId = $user->tenant_id;

        $diagnosticsQuery = Diagnostic::query();

        if ($role !== 'superadmin') {
            $diagnosticsQuery->whereHas('tenants', function ($query) use ($tenantId) {
                $query->where('tenants.id', $tenantId);
            });
        }

        $diagnostics = $diagnosticsQuery->with([
            'periods' => function ($query) use ($tenantId, $role) {
                if ($role !== 'superadmin') {
                    $query->where('tenant_id', $tenantId);
                }
            },
            'periods.tenant',
            'tenants' => function ($query) use ($tenantId, $role) {
                if ($role !== 'superadmin') {
                    $query->where('tenants.id', $tenantId);
                }
            },
            'questions'
        ])->get();

        $diagnosticData = collect();

        foreach ($diagnostics as $diagnostic) {
            $period = $diagnostic->periods
                ->filter(fn($p) => 
                    $p->tenant_id == $tenantId &&
                    $now->between(Carbon::parse($p->start), Carbon::parse($p->end))
                )
                ->first();

            $questionsForUser = $diagnostic->questions->where('target', $role);

            $hasQuestions = $questionsForUser->isNotEmpty();
            $hasAnswered = false;

            if ($period && $hasQuestions) {
                $hasAnswered = Answer::where('diagnostic_id', $diagnostic->id)
                    ->where('diagnostic_period_id', $period->id)
                    ->where('user_id', $user->id)
                    ->exists();
            }

            $hasAnsweredAnyPeriod = Answer::where('diagnostic_id', $diagnostic->id)
                ->where('user_id', $user->id)
                ->exists();

            $isAvailable = $period && !$hasAnswered && $hasQuestions;

            $diagnosticData->push([
                'diagnostic'            => $diagnostic,
                'period'                => $period,
                'questions'             => $questionsForUser,
                'hasQuestions'          => $hasQuestions,
                'hasAnswered'           => $hasAnswered,
                'hasAnsweredAnyPeriod'  => $hasAnsweredAnyPeriod,
                'isAvailable'           => $isAvailable,
            ]);
        }

        return view('diagnostic.index', [
            'user'                  => $user,
            'availableDiagnostics'  => $diagnosticData->where('isAvailable', true),
            'diagnostics'           => $diagnosticData->where('isAvailable', false)
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $tenants = Tenant::all();

        return view ('diagnostic.create', compact('tenants'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'                  => 'required|string',
            'description'            => 'nullable|string',
            'questions'              => 'required|array',
            'questions.*.text'       => 'required|string',
            'questions.*.category'   => 'required|string',
            'questions.*.target'     => 'required|in:admin,user',
            'tenants'                => 'required|array',
            'tenants.*'              => 'exists:tenants,id',
            'start'                  => 'required|date',
            'end'                    => 'required|date|after_or_equal:start'
        ]);

        $diagnostic = Diagnostic::create([
            'title'       => $request->title,
            'description' => $request->description
        ]);
        
        $diagnostic->tenants()->sync($request->tenants);

        foreach ($request->questions as $q) {
            $diagnostic->questions()->create([
                'text' => $q['text'],
                'category' => $q['category'],
                'target' => $q['target']
            ]);
        }

        foreach ($request->tenants as $tenantId) {
            $diagnostic->periods()->create([
                'tenant_id' => $tenantId,
                'start'     => $request->start,
                'end'       => $request->end
            ]);
        }

        return redirect()->route('diagnostico.index')->with('success', 'Diagnóstico criado com sucesso.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Diagnostic $diagnostic)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $id)
    {
        $diagnostic = Diagnostic::with('questions', 'tenants', 'periods')->findOrFail($id);

        $linkedTenants = $diagnostic->tenants;
        
        $allTenants = Tenant::all();

        $periodsByTenant = [];

        foreach ($linkedTenants as $tenant) {
            $lastPeriod = $diagnostic->periods
                ->where('tenant_id', $tenant->id)
                ->sortByDesc('end')
                ->first();

            $periodsByTenant[$tenant->id] = $lastPeriod;
        }

        return view('diagnostic.edit', compact(
            'diagnostic',
            'linkedTenants',
            'allTenants',
            'periodsByTenant'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $diagnostic = Diagnostic::with('periods', 'tenants')->findOrFail($id);

        $rules = [
            'title'                => 'required|string',
            'description'          => 'nullable|string',
            'questions'            => 'required|array',
            'questions.*.text'     => 'required|string',
            'questions.*.category' => 'required|string',
            'questions.*.target'   => 'required|in:admin,user',
            'tenants'              => 'required|array',
            'tenants.*'            => 'exists:tenants,id',
            'question_ids'         => 'array',
            'question_ids.*'       => 'nullable|integer|exists:questions,id',
            'tenant_ids'           => 'required|array',
        ];

        foreach ($request->input('tenant_ids', []) as $tenantId) {
            $rules["start.$tenantId"] = 'required|date';
            $rules["end.$tenantId"] = 'required|date|after_or_equal:start.' . $tenantId;
        }

        $request->validate($rules);

        $diagnostic->update([
            'title'       => $request->title,
            'description' => $request->description,
        ]);

        $submittedQuestions = $request->input('questions', []);
        $submittedQuestionIds = $request->input('question_ids', []);
        $existingQuestionIds = $diagnostic->questions()->pluck('id')->toArray();
        $processedIds = [];

        foreach ($submittedQuestions as $index => $questionData) {
            $questionId = $submittedQuestionIds[$index] ?? null;

            if ($questionId && in_array($questionId, $existingQuestionIds)) {
                $diagnostic->questions()->find($questionId)?->update([
                    'text'     => $questionData['text'],
                    'category' => $questionData['category'],
                    'target'   => $questionData['target'],
                ]);
                $processedIds[] = $questionId;
            } else {
                $new = $diagnostic->questions()->create([
                    'text'     => $questionData['text'],
                    'category' => $questionData['category'],
                    'target'   => $questionData['target'],
                ]);
                $processedIds[] = $new->id;
            }
        }

        $toDelete = array_diff($existingQuestionIds, $processedIds);
        if (!empty($toDelete)) {
            $diagnostic->questions()->whereIn('id', $toDelete)->delete();
        }

        $selectedTenantIds = $request->input('tenants', []);
        $tenantIdsFromForm = $request->input('tenant_ids', []);
        $starts = $request->input('start', []);
        $ends = $request->input('end', []);

        $currentTenantIds = $diagnostic->tenants()->pluck('tenants.id')->toArray();
        $removedTenantIds = array_diff($currentTenantIds, $selectedTenantIds);

        if (!empty($removedTenantIds)) {
            $diagnostic->tenants()->detach($removedTenantIds);

            $diagnostic->periods()->whereIn('tenant_id', $removedTenantIds)->delete();

            Answer::where('diagnostic_id', $diagnostic->id)
                ->whereIn('tenant_id', $removedTenantIds)
                ->delete(); 
        }


        $diagnostic->tenants()->sync($selectedTenantIds);

        foreach ($tenantIdsFromForm as $tenantId) {
            if (!in_array($tenantId, $selectedTenantIds)) {
                continue;
            }

            $start = isset($starts[$tenantId]) ? Carbon::parse($starts[$tenantId]) : null;
            $end = isset($ends[$tenantId]) ? Carbon::parse($ends[$tenantId]) : null;

            if ($start && $end) {
                $period = $diagnostic->periods()
                    ->where('tenant_id', $tenantId)
                    ->first();

                if ($period) {
                    $period->update([
                        'start' => $start,
                        'end'   => $end,
                    ]);
                } else {
                    $diagnostic->periods()->create([
                        'tenant_id' => $tenantId,
                        'start'     => $start,
                        'end'       => $end,
                    ]);
                }
            }
        }

        return redirect()->route('diagnostico.index')->with('success', 'Diagnóstico atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $diagnostic = Diagnostic::with('questions', 'answers', 'tenants', 'periods')->findOrFail($id);

        $diagnostic->delete();

        return redirect()->route('diagnostico.index')->with('success', 'Diagnóstico excluído com sucesso!');
    }

    // public function available() {
    //     $diagnosticsQuery = Diagnostic::whereNull('tenant_id')->with('questions')->get();

    //     return view ('diagnostic.availables', compact('diagnostics'));
    // }

    public function showAnswerForm(string $id) {
        $user = Auth::user();
        $role = $user->role;

        $diagnostic = Diagnostic::with([
            'questions' => function ($query) use ($role) {
                $query->where('target', $role);
            },
            'tenants',
            'periods'
        ])->findOrFail($id);

        if (!$diagnostic->tenants->contains('id', $user->tenant_id)) {
            abort(403, 'Você não tem permissão para acessar esse diagnóstico.');
        }

        $currentPeriod = $diagnostic->periods
            ->where('tenant_id', $user->tenant_id)
            ->filter(fn($p) => now()->between(Carbon::parse($p->start), Carbon::parse($p->end)))
            ->first();
            
        if (!$currentPeriod) {
            return redirect()->route('diagnostico.index')->with('error', 'Este diagnóstico não está disponível no momento.');    
        }

        $alreadyAnswered = Answer::where('diagnostic_id', $diagnostic->id)
            ->where('diagnostic_period_id', $currentPeriod->id)
            ->where('user_id', $user->id)
            ->exists();

        if ($alreadyAnswered) {
            return redirect()->route('diagnostico.index')
                ->with('error', 'Você já respondeu este diagnóstico.');
        }

        return view ('diagnostic.availables', compact('diagnostic', 'currentPeriod'));
    }

    public function submitAnswer(Request $request, string $id) {
        $diagnostic = Diagnostic::with('questions', 'periods')->findOrFail($id);
        $user = Auth::user();

        $currentPeriod = $diagnostic->periods()
            ->whereDate('start', '<=', now())
            ->whereDate('end', '>=', now())
            ->where('tenant_id', $user->tenant_id)
            ->latest('start')
            ->first();

        if (!$currentPeriod) {
            return redirect()->route('diagnostico.available')->with('error', 'Não há período de resposta ativo.');
        }

        $alreadyAnswered = Answer::where('diagnostic_id', $diagnostic->id)
            ->where('diagnostic_period_id', $currentPeriod->id)
            ->where('tenant_id', $user->tenant_id)
            ->where('user_id', $user->id)
            ->exists();

        if ($alreadyAnswered) {
            return redirect()->route('diagnostico.available')
                ->with('error', 'Este diagnóstico já foi respondido neste período.');
        }

        $request->validate([
            'answers'   => 'required|array',
            'answers.*' => 'required|integer|min:1|max:5'
        ]);

        $validQuestionIds = $diagnostic->questions()
            ->where('target', $user->role)
            ->pluck('id')
            ->toArray(); 

        foreach ($request->answers as $questionId => $note) {
            if (!in_array($questionId, $validQuestionIds)) {
                continue;
            }

            Answer::create([
                'user_id'                  => $user->id,
                'diagnostic_id'            => $diagnostic->id,
                'question_id'              => $questionId,
                'note'                     => $note,
                'tenant_id'                => $user->tenant_id,
                'diagnostic_period_id'     => $currentPeriod->id
            ]);
        }
        
        return redirect()->route('diagnostico.index')->with('success', 'Respostas enviadas com sucesso!');
    }

    public function reabrir(Request $request, string $id) {
        $user = auth()->user();

        if ($user->role !== 'superadmin') {
            abort(403, 'Acesso não autorizado.');
        }

        $diagnostic = Diagnostic::with('periods')->findOrFail($id);
        $tenantId = $request->get('tenant');

        if (!$tenantId || !$diagnostic->tenants->contains('id', $tenantId)) {
            return back()->with('error', 'Empresa não vinculada ao diagnóstico.');
        }

        $lastPeriod = $diagnostic->periods
            ->where('tenant_id', $tenantId)
            ->sortByDesc('end')
            ->first();

        $start = $lastPeriod
            ? Carbon::parse($lastPeriod->end)->addDay()
            : now()->startOfDay();

        $end =  (clone $start)->addDays(7);

        $diagnostic->periods()->create([
            'start'     => $start,
            'end'       => $end,
            'tenant_id' => $tenantId
        ]);
        
        return back()->with('success', 'Novo período de resposta liberado!');
    }
}

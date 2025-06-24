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

        $diagnostics = Diagnostic::query();

        if ($user->role !== 'superadmin') {
            $diagnostics->whereHas('tenants', function ($query) use ($tenantId) {
                $query->where('tenants.id', $tenantId);
            });
        }

        $diagnostics = $diagnostics->with([
            'periods', 
            'answers', 
            'tenants',
            'questions' => function ($query) use ($role) {
                $query->where('target', $role);
            }
        ])
        ->get();

        $availableDiagnostics = collect();
        
        foreach ($diagnostics as $diagnostic) {
            $period = $diagnostic->periods
                ->where('tenant_id', $tenantId)
                ->filter(fn($p) => $now->between(Carbon::parse($p->start), Carbon::parse($p->end)))
                ->first();
            
            if (!$period) {
                continue;
            }

            $alreadyAnswered = $diagnostic->answers
                ->where('user_id', $user->id)
                ->where('diagnostic_period_id', $period->id)
                ->isNotEmpty();

            if (!$alreadyAnswered) {
                $availableDiagnostics->push($diagnostic);
            }
        }

        $diagnosticsNotAvailable = $diagnostics->filter(fn($d) => !$availableDiagnostics->contains($d));

        return view ('diagnostic.index', [
            'availableDiagnostics' => $availableDiagnostics,
            'diagnostics'          => $diagnosticsNotAvailable
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
    public function edit(string $id)
    {
        $diagnostic = Diagnostic::with('questions', 'tenants', 'periods')->findOrFail($id);
        $tenants = Tenant::all();

        return view ('diagnostic.edit', compact('diagnostic', 'tenants'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $diagnostic = Diagnostic::with('periods')->findOrFail($id);

        $request->validate([
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
            'start'                => 'required|date',
            'end'                  => 'required|date|after_or_equal:start'
        ]);

        $diagnostic->update([
            'title'       => $request->title,
            'description' => $request->description
        ]);

        $diagnostic->tenants()->sync($request->tenants);

        $submittedQuestions  = $request->input('questions', []);
        $submittedQuestionIds = $request->input('question_ids', []);
        $existingQuestionIds = $diagnostic->questions()->pluck('id')->toArray();
        $processedIds = [];

        foreach ($request->questions as $index => $questionData) {
            $questionId = $submittedQuestionIds[$index] ?? null;

            if ($questionId && in_array($questionId, $existingQuestionIds)) {
                $diagnostic->questions()->find($questionId)?->update([
                    'text' => $questionData['text'],
                    'category' => $questionData['category'],
                    'target' => $questionData['target']
                ]);
                $processedIds[] = $questionId;
            } else {
                $new = $diagnostic->questions()->create([
                    'text' => $questionData['text'],
                    'category' => $questionData['category'],
                    'target' => $questionData['target']
                ]);
                $processedIds[] = $new->id;
            }
        }

        $toDelete = array_diff($existingQuestionIds, $processedIds);
        if (!empty($toDelete)) {
            $diagnostic->questions()->whereIn('id', $toDelete)->delete();
        }

        foreach ($request->tenants as $tenantId) {
            $start = Carbon::parse($request->start);
            $end   = Carbon::parse($request->end);

            $existingPeriod = $diagnostic->periods()->where('tenant_id', $tenantId)->first();

            if ($existingPeriod) {
                $existingStart = Carbon::parse($existingPeriod->start);
                $existingEnd   = Carbon::parse($existingPeriod->end);

                $isDifferent = !$existingStart->equalTo($start) || !$existingEnd->equalTo($end);

                if (!$isDifferent) {
                    $existingPeriod->update([
                        'start' => $start,
                        'end'   => $end
                    ]);
                }
            } else {
                $diagnostic->periods()->create([
                    'tenant_id' => $tenantId,
                    'start'     => $start,
                    'end'       => $end
                ]);
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
    //     $diagnostics = Diagnostic::whereNull('tenant_id')->with('questions')->get();

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

        $currentPeriod = $diagnostic->periods()
            ->where('tenant_id', $user->tenant_id)
            ->whereDate('start', '<=', now())
            ->whereDate('end', '>=', now())
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

        $end = $start->copy()->addDays(30);

        $alreadyExists = $diagnostic->periods()
            ->where('tenant_id', $tenantId)
            ->whereDate('start', $start)
            ->whereDate('end', $end)
            ->exists();

        if ($alreadyExists) {
            return back()->with('info', 'Esse período já está configurado para essa empresa.');
        }

        $diagnostic->periods()->create([
            'start'     => $start,
            'end'       => $end,
            'tenant_id' => $tenantId
        ]);

        return back()->with('success', 'Novo período de resposta liberado!');
    }
}

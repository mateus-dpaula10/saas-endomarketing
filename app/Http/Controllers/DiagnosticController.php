<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Diagnostic;
use Illuminate\Http\Request;
use App\Models\Answer;
use App\Models\Tenant;
use App\Models\Question;
use App\Models\DiagnosticPeriod;
use App\Models\StandardCampaign;
use App\Models\Campaign;
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

            $questionsForUser = $diagnostic->questions->filter(function ($question) use ($role) {
                return $question->pivot && $question->pivot->target === $role;
            });

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

        $questions = Question::select('id', 'text', 'category')->get();

        $perguntasPorCategoria = $questions->groupBy('category')->map(function ($items) {
            return $items->map(fn($q) => ['id' => $q->id, 'text' => $q->text])->values();
        });

        return view ('diagnostic.create', compact('tenants', 'perguntasPorCategoria'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'                  => 'required|string',
            'description'            => 'nullable|string',
            'questions_text'         => 'required|array',
            'questions_text.*'       => 'nullable|exists:questions,id',
            'questions_custom'       => 'array',
            'questions_custom.*'     => 'nullable|string',
            'questions_target'       => 'required|array',
            'questions_target.*'     => 'required|in:admin,user',
            'questions_category'     => 'required|array',
            'questions_category.*'   => 'nullable|string',
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

        foreach ($request->questions_text as $index => $questionId) {
            $text = null;
            $target = $request->questions_target[$index] ?? null;
            $category = $request->questions_category[$index] ?? null;

            if ($questionId) {
                $diagnostic->questions()->attach($questionId, ['target' => $target]);
            } else {
                $text = $request->questions_custom[$index] ?? null;

                if ($text) {
                    $newQuestion = Question::create([
                        'text' => $text,
                        'category' => $category,
                        'target' => $target,
                        'diagnostic_id' => $diagnostic->id,
                    ]);
                    $diagnostic->questions()->attach($newQuestion->id, ['target' => $target]);
                }
            }
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

        $questions = Question::whereNull('diagnostic_id')->get();

        $perguntasPorCategoria = $questions->groupBy('category')->map(function ($items) {
            return $items->map(fn($q) => [
                'id' => $q->id,
                'text' => $q->text
            ])->values();
        });

        return view('diagnostic.edit', compact(
            'diagnostic',
            'linkedTenants',
            'allTenants',
            'periodsByTenant',
            'perguntasPorCategoria'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $diagnostic = Diagnostic::with(['questions', 'periods', 'tenants'])->findOrFail($id);

        $rules = [
            'title'                  => 'required|string',
            'description'            => 'nullable|string',
            'questions_text'         => 'required|array',
            'questions_text.*'       => 'nullable|exists:questions,id',
            'questions_custom'       => 'array',
            'questions_custom.*'     => 'nullable|string',
            'questions_target'       => 'required|array',
            'questions_target.*'     => 'required|in:admin,user',
            'questions_category'     => 'required|array',
            'questions_category.*'   => 'nullable|string',
            'tenants'                => 'required|array',
            'tenants.*'              => 'exists:tenants,id',
            'tenant_ids'             => 'required|array',
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

        $diagnostic->questions()->detach();

        foreach ($request->questions_text as $index => $questionId) {
            $target   = $request->questions_target[$index] ?? 'admin';
            $category = $request->questions_category[$index] ?? null;
            $text     = $request->questions_custom[$index] ?? null;

            if ($questionId) {
                $diagnostic->questions()->attach($questionId, ['target' => $target]);
            } elseif ($text) {
                $newQuestion = Question::create([
                    'text'           => $text,
                    'category'       => $category,
                    'target'         => $target,
                    'diagnostic_id'  => $diagnostic->id,
                ]);
                $diagnostic->questions()->attach($newQuestion->id, ['target' => $target]);
            }
        }
        
        $selectedTenantIds = $request->input('tenants', []);
        $diagnostic->tenants()->sync($selectedTenantIds);

        $diagnostic->periods()
            ->whereNotIn('tenant_id', $selectedTenantIds)
            ->delete();

        foreach ($selectedTenantIds as $tenantId) {
            $start = $request->start[$tenantId] ?? null;
            $end   = $request->end[$tenantId] ?? null;

            if ($start && $end) {
                $period = $diagnostic->periods()->firstOrNew(['tenant_id' => $tenantId]);
                $period->start = Carbon::parse($start);
                $period->end   = Carbon::parse($end);
                $period->save();
            }
        }

        return redirect()->route('diagnostico.index')->with('success', 'Diagnóstico atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $diagnostic = Diagnostic::with('questions', 'answers', 'tenants', 'periods', 'campaigns')->findOrFail($id);

        $diagnostic->delete();

        return redirect()->route('diagnostico.index')->with('success', 'Diagnóstico excluído com sucesso!');
    }

    public function available() {
        $diagnostics = Diagnostic::whereNull('tenant_id')->with('questions')->get();
        return view('diagnostics.available', compact('diagnostics'));
    }

    public function showAnswerForm(string $id) {
        $user = Auth::user();
        $role = $user->role;

        $diagnostic = Diagnostic::with([
            'questions' => function ($query) use ($role) {
                $query->where('diagnostic_question.target', $role);
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
        $user = Auth::user();

        $diagnostic = Diagnostic::with('questions.options', 'periods')
            ->whereHas('tenants', function ($q) use ($user) {
                $q->where('tenants.id', $user->tenant_id);
            })
            ->findOrFail($id);

        $currentPeriod = $diagnostic->periods()
            ->whereDate('start', '<=', now())
            ->whereDate('end', '>=', now())
            ->where('tenant_id', $user->tenant_id)
            ->latest('start')
            ->first();

        if (!$currentPeriod) {
            return redirect()->route('diagnostico.index')->with('error', 'Não há período de resposta ativo.');
        }

        $alreadyAnswered = Answer::where('diagnostic_id', $diagnostic->id)
            ->where('diagnostic_period_id', $currentPeriod->id)
            ->where('tenant_id', $user->tenant_id)
            ->where('user_id', $user->id)
            ->exists();

        if ($alreadyAnswered) {
            return redirect()->route('diagnostico.index')
                ->with('error', 'Este diagnóstico já foi respondido neste período.');
        }

        $request->validate([
            'answers'   => 'required|array',
            'answers.*' => 'required|integer|min:1|max:5'
        ]);

        $questionsForRole = $diagnostic->questions->filter(fn($q) => $q->pivot && $q->pivot->target === $user->role);
        $validQuestionIds = $questionsForRole->pluck('id')->toArray();

        foreach ($request->answers as $questionId => $note) {
            if (!in_array($questionId, $validQuestionIds)) continue;

            $question = $questionsForRole->firstWhere('id', $questionId);
            $validValues = $question->options->pluck('value')->toArray();

            if (!in_array((int)$note, $validValues)) continue;

            Answer::create([
                'user_id'              => $user->id,
                'diagnostic_id'        => $diagnostic->id,
                'question_id'          => $questionId,
                'note'                 => $note,
                'tenant_id'            => $user->tenant_id,
                'diagnostic_period_id' => $currentPeriod->id
            ]);
        }

        $respostas = Answer::where('user_id', $user->id)
            ->where('diagnostic_id', $diagnostic->id)
            ->where('diagnostic_period_id', $currentPeriod->id)
            ->with('question')
            ->get();
            
        $notasPorCategoria = $respostas->groupBy(fn($a) => $a->question->category)
            ->map(fn($grupo) => round($grupo->avg('note'), 1));
            
        foreach ($notasPorCategoria as $categoria => $nota) {
            Campaign::where('tenant_id', $user->tenant_id)
                ->where('diagnostic_id', $diagnostic->id)
                ->where('is_auto', true)
                ->whereHas('standardCampaign', function ($query) use ($categoria) {
                    $query->where('category_code', $categoria);
                })->delete();

            $campanhaPadrao = StandardCampaign::where('category_code', $categoria)
                ->where('trigger_max_score', '>=', $nota)
                ->where('is_active', true)
                ->orderBy('trigger_max_score')
                ->first();

            if ($campanhaPadrao) {
                Campaign::create([
                    'tenant_id'            => $user->tenant_id,
                    'standard_campaign_id' => $campanhaPadrao->id,
                    'diagnostic_id'        => $diagnostic->id,
                    'text'                 => $campanhaPadrao->text,
                    'description'          => $campanhaPadrao->description,
                    'start_date'           => now(),
                    'end_date'             => now()->addWeeks(3),
                    'is_auto'              => true,
                    'is_manual'            => false
                ]);
            }
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

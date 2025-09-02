<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Diagnostic;
use Illuminate\Http\Request;
use App\Models\Answer;
use App\Models\Tenant;
use App\Models\Question;
use App\Models\StandardCampaign;
use App\Models\Campaign;
use App\Models\Plain;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class DiagnosticController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index() {
        $authUser = auth()->user();

        $diagnostics = Diagnostic::with(['questions.options', 'tenants', 'campaigns', 'plain'])->get();

        return view ('diagnostic.index', compact('authUser', 'diagnostics'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $id)
    {
        $diagnostic = Diagnostic::with('questions', 'tenants')->findOrFail($id);

        $linkedTenants = $diagnostic->tenants;

        $allTenants = Tenant::where('plain_id', $diagnostic->plain_id)->get();

        $questions = Question::whereDoesntHave('diagnostics', function ($q) use ($diagnostic) {
            $q->where('diagnostics.id', $diagnostic->id);
        })->get();

        if ($diagnostic->type === 'cultura') {
            $categorias = [
                'identidade_proposito' => 'Identidade e Propósito',
                'valores_comportamentos' => 'Valores e Comportamentos',
                'ambiente_clima' => 'Ambiente e Clima',
                'comunicacao_lideranca' => 'Comunicação e Liderança',
                'processos_praticas' => 'Processos e Práticas',
                'reconhecimento_celebracao' => 'Reconhecimento e Celebração',
                'diversidade_pertencimento' => 'Diversidade e Pertencimento',
                'aspiracoes_futuro' => 'Aspirações e Futuro'
            ];
        } elseif ($diagnostic->type === 'comunicacao' || $diagnostic->type === 'comunicacao_campanhas') {
            $categorias = [
                'contratar' => 'Contratar',
                'celebrar' => 'Celebrar',
                'compartilhar' => 'Compartilhar',
                'inspirar' => 'Inspirar',
                'falar' => 'Falar',
                'escutar' => 'Escutar',
                'cuidar' => 'Cuidar',
                'desenvolver' => 'Desenvolver',
                'agradecer' => 'Agradecer'
            ];
        } else {
            $categorias = []; 
        }

        $perguntasPorCategoria = $questions->groupBy('category')->map(function ($items) {
            return $items->map(fn($q) => [
                'id'     => $q->id,
                'text'   => $q->text,
                'type'   => $q->type,
            ])->values();
        });

        $plains = Plain::all();

        return view('diagnostic.edit', compact(
            'diagnostic',
            'linkedTenants',
            'allTenants',
            'perguntasPorCategoria',
            'plains',
            'categorias'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $diagnostic = Diagnostic::with('questions')->findOrFail($id);

        $request->validate([
            'title'                       => 'required|string',
            'description'                 => 'nullable|string',
            'questions_text'              => 'required|array',
            'questions_text.*'            => 'nullable|string',
            'questions_category'          => 'required|array',
            'questions_category.*'        => 'nullable|string',
            'questions_type'              => 'required|array',
            'questions_type.*'            => 'in:aberta,fechada',
            'questions_target'            => 'required|array',
            'questions_target.*.*'        => 'required|in:admin,user',
            'questions_options'           => 'array',
        ], [
            'title.required'              => 'O título do diagnóstico é obrigatório.',
            'title.string'                => 'O título deve ser uma string válida.',
            'questions_text.required'     => 'É necessário adicionar pelo menos uma pergunta.',
            'questions_text.*.string'     => 'Cada pergunta deve ser um texto válido.',
            'questions_category.required' => 'Cada pergunta precisa ter uma categoria.',
            'questions_type.required'     => 'O tipo da pergunta é obrigatório.',
            'questions_type.*.in'         => 'O tipo da pergunta deve ser "aberta" ou "fechada".',
            'questions_target.required'   => 'É necessário selecionar pelo menos um público-alvo.',
            'questions_target.*.*.in'     => 'O público-alvo deve ser "Administrador" ou "Colaborador".',
            'questions_options.array'     => 'As opções da pergunta devem ser um array válido.',
        ]);


        $diagnostic->update([
            'title'       => $request->title,
            'description' => $request->description,
            'plain_id'    => $request->plain_id
        ]);

        $diagnostic->questions()->detach();

        foreach ($request->questions_text as $index => $text) {
            $type     = $request->questions_type[$index] ?? 'aberta';
            $category  = $request->questions_category[$index] ?? null;
            $targets   = $request->questions_target[$index] ?? [];

            $questionId = $request->question_ids[$index] ?? null;

            if ($questionId) {
                $question = Question::find($questionId);
                $question->update([
                    'text'     => $text,
                    'category' => $category,
                    'type'     => $type
                ]);
            } else {
                $question = Question::create([
                    'text'     => $text,
                    'category' => $category,
                    'type'     => $type,
                ]);
            }
                
            $diagnostic->questions()->attach($question->id, [
                'target' => json_encode($targets)
            ]);

            if ($type === 'fechada') {
                $submittedOptions = $request->questions_options[$index] ?? [];
                $existingOptions = $question->options()->pluck('id')->toArray();
                $submittedIds = [];

                foreach ($submittedOptions as $opt) {
                    if (isset($opt['id']) && in_array($opt['id'], $existingOptions)) {
                        $option = $question->options()->find($opt['id']);
                        $option->update([
                            'text'   => $opt['text'],
                            'weight' => $opt['weight'],
                        ]);
                        $submittedIds[] = $option->id;
                    } else {
                        $option = $question->options()->create([
                            'text'   => $opt['text'],
                            'weight' => $opt['weight'],
                        ]);
                        $submittedIds[] = $option->id;
                    }
                }

                $question->options()->whereNotIn('id', $submittedIds)->delete();
            } else {
                $question->options()->delete();
            }
        }

        return redirect()->route('diagnostico.index')->with('success', 'Diagnóstico atualizado com sucesso!');
    }

    public function empresasPorPlano($plainId) {
        $tenants = Tenant::where('plain_id', $plainId)->get(['id', 'nome']);
        return response()->json($tenants);
    }

    public function getPeriodsByPlain($plainId) {
        $tenants = Plain::findOrFail($plainId)->tenants; 
        $diagnostic = Diagnostic::where('plain_id', $plainId)->first(); 

        $result = [];

        foreach ($tenants as $tenant) {
            $period = $diagnostic
                ? $diagnostic->periods()->where('tenant_id', $tenant->id)->latest()->first()
                : null;

            $result[$tenant->id] = $period ? [
                'start' => optional($period->start)->format('Y-m-d'),
                'end' => optional($period->end)->format('Y-m-d'),
            ] : null;
        }

        return response()->json($result);
    }

    public function getPerguntasPorPlano($plainId, $diagnosticId) {   
        $diagnostic = Diagnostic::with('questions')->findOrFail($diagnosticId);
        $perguntasAssociadasIds = $diagnostic->questions->pluck('id')->toArray();

        $questionsNaoAssociadas = Question::whereNotIn('id', $perguntasAssociadasIds)->get();

        $allQuestions = $diagnostic->questions->concat($questionsNaoAssociadas);

        $perguntasPorCategoria = $allQuestions->groupBy('category')->map(function ($items) {
            return $items->map(fn($q) => [
                'id' => $q->id,
                'text' => $q->text,
            ])->values();
        });

        return response()->json($perguntasPorCategoria);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $diagnostic = Diagnostic::with('questions.options')->findOrFail($id);

        foreach ($diagnostic->questions as $question) {
            if ($question->diagnostics()->where('diagnostics.id', '!=', $diagnostic->id)->count() === 0) {
                $question->options()->delete();
                $question->delete();
            }
        }

        $diagnostic->questions()->detach();

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
            'questions',
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

        $questions = $diagnostic->questions->filter(function ($question) use ($role) {
            $targetJson = $question->pivot->target;

            if (!is_string($targetJson) || !Str::startsWith($targetJson, '[')) {
                return false; 
            }

            $targets = json_decode($targetJson, true);

            return is_array($targets) && in_array($role, $targets);
        });

        return view ('diagnostic.availables', compact('diagnostic', 'currentPeriod', 'questions'));
    }

    public function submitAnswer(Request $request, string $id) {
        $user = Auth::user();

        $diagnostic = Diagnostic::with('questions', 'periods')
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

        $questionsForRole = $diagnostic->questions->filter(function ($q) use ($user) {
            if (!$q->pivot || !$q->pivot->target) return false;

            $target = is_string($q->pivot->target)
                ? json_decode($q->pivot->target, true)
                : $q->pivot->target;

            return is_array($target) && in_array($user->role, $target);
        });
        $validQuestionIds = $questionsForRole->pluck('id')->toArray();

        foreach ($request->answers as $questionId => $note) {
            if (!in_array($questionId, $validQuestionIds)) continue;

            if (!in_array((int)$note, [1, 2, 3, 4, 5])) continue;

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

        $plainId = $user->tenant->plain_id ?? 1;

        $planoConfig = [
            2 => ['count' => 2, 'duration' => 15],
            3 => ['count' => 3, 'duration' => 10]
        ];

        if (isset($planoConfig[$plainId])) {
            $config = $planoConfig[$plainId];

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
                    $startDate = now();

                    for ($i = 0; $i < $config['count']; $i++) {
                        $endDate = $startDate->copy()->addDays($config['duration']);

                        Campaign::create([
                            'tenant_id'            => $user->tenant_id,
                            'standard_campaign_id' => $campanhaPadrao->id,
                            'diagnostic_id'        => $diagnostic->id,
                            'text'                 => $campanhaPadrao->text,
                            'description'          => $campanhaPadrao->description,
                            'start_date'           => $startDate,
                            'end_date'             => $endDate,
                            'is_auto'              => true,
                            'is_manual'            => false
                        ]);

                        $startDate = $endDate->copy()->addDay();
                    }
                }
            }            
        }

        return redirect()->route('diagnostico.index')->with('success', 'Respostas enviadas com sucesso!');
    }
}

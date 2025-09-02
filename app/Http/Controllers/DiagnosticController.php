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
    public function index() {
        $authUser = auth()->user();

        $diagnostics = Diagnostic::with(['questions.options', 'campaigns', 'plain'])->get();

        if ($authUser->role === 'superadmin') {
            $diagnosticsFiltered = $diagnostics->map(function ($diagnostic) use ($authUser) {
                return [
                    'diagnostic'   => $diagnostic,
                    'hasAnswered'  => false,
                    'hasQuestions' => $diagnostic->questions->isNotEmpty()
                ];
            });
        } else {
            $tenantPlainId = $authUser->tenant->plain_id ??null;
            
            $diagnosticsFiltered = $diagnostics->filter(function ($diagnostic) use ($tenantPlainId) {
                return $diagnostic->plain_id === $tenantPlainId;
            })->map(function ($diagnostic) use ($authUser) {
                $hasAnswered = \App\Models\Answer::where('diagnostic_id', $diagnostic->id)
                    ->where('user_id', $authUser->id)
                    ->exists();

                $answersGrouped = [];
                
                if ($authUser->role === 'admin') {
                    $answersGrouped = $diagnostic->questions->map(function ($question) use ($diagnostic, $authUser) {
                        $answers = Answer::where('diagnostic_id', $diagnostic->id)
                            ->where('question_id', $question->id)
                            ->where('tenant_id', $authUser->tenant_id)
                            ->get();

                        if ($question->type === 'fechada') {
                            return [
                                'question' => $question,
                                'average'  => $answers->avg('note'),
                                'answers'  => []
                            ];
                        }

                        return [
                            'question'     => $question,
                            'average'      => null,
                            'answers'      => $answers->pluck('text')
                        ];
                    });
                }

                return [
                    'diagnostic'     => $diagnostic,
                    'hasAnswered'    => $hasAnswered,
                    'hasQuestions'   => $diagnostic->questions->isNotEmpty(),
                    'answersGrouped' => $answersGrouped
                ];
            });
        }

        return view ('diagnostic.index', compact('authUser', 'diagnosticsFiltered'));
    }

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

    public function answer(Diagnostic $diagnostic) {
        return view ('diagnostic.answer', compact('diagnostic'));
    }

    public function submitAnswers(Diagnostic $diagnostic, Request $request) {
        $authUser = auth()->user();

        $answersData = $request->input('answers', []);

        foreach ($answersData as $questionId => $answer) {
            $question = Question::find($questionId);
            if (!$question) continue;

            if ($question->type === 'fechada' && isset($answer['note'])) {
                Answer::updateOrCreate(
                    [
                        'diagnostic_id' => $diagnostic->id,
                        'question_id'   => $questionId,
                        'user_id'       => $authUser->id,
                        'tenant_id'     => $authUser->tenant->id
                    ],
                    [
                        'note'          => $answer['note']
                    ]
                );
            }

            if ($question->type === 'aberta' && isset($answer['text'])) {
                Answer::updateOrCreate(
                    [
                        'diagnostic_id' => $diagnostic->id,
                        'question_id'   => $questionId,
                        'user_id'       => $authUser->id,
                        'tenant_id'     => $authUser->tenant->id
                    ],
                    [
                        'note'          => null,
                        'text'          => $answer['text']
                    ]
                );
            }
        }

        return redirect()->route('diagnostico.index')->with('success', 'Diagnóstico respondido com sucesso.');
    }
}

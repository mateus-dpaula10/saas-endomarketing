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
use App\Services\OpenAIService;

class DiagnosticController extends Controller
{
    public function index(OpenAIService $openAIService) {
        $authUser = auth()->user();

        $categoriaFormatada = [
            'identidade_proposito'      => 'Identidade e propósito',
            'valores_comportamentos'    => 'Valores e comportamentos',
            'ambiente_clima'            => 'Ambiente e clima',
            'comunicacao_lideranca'     => 'Comunicação e liderança',
            'processos_praticas'        => 'Processos e práticas',
            'reconhecimento_celebracao' => 'Reconhecimento e celebração',
            'diversidade_pertencimento' => 'Diversidade e pertencimento',
            'aspiracoes_futuro'         => 'Aspirações futuras',
            'contratar'                 => 'Contratação',
            'celebrar'                  => 'Celebração',
            'compartilhar'              => 'Compartilhar informações',
            'inspirar'                  => 'Inspiração da liderança',
            'falar'                     => 'Falar abertamente',
            'escutar'                   => 'Escuta da liderança',
            'cuidar'                    => 'Cuidado com pessoas',
            'desenvolver'               => 'Desenvolvimento',
            'agradecer'                 => 'Agradecimento'
        ];

        $mapaQuadrantes = [
            'identidade_proposito'      => 'Clã',
            'valores_comportamentos'    => 'Clã',
            'ambiente_clima'            => 'Clã',
            'comunicacao_lideranca'     => 'Adhocracia',
            'processos_praticas'        => 'Hierárquica',
            'reconhecimento_celebracao' => 'Clã',
            'diversidade_pertencimento' => 'Clã',
            'aspiracoes_futuro'         => 'Adhocracia',
            'contratar'                 => 'Mercado',
            'celebrar'                  => 'Clã',
            'compartilhar'              => 'Adhocracia',
            'inspirar'                  => 'Adhocracia',
            'falar'                     => 'Clã',
            'escutar'                   => 'Clã',
            'cuidar'                    => 'Clã',
            'desenvolver'               => 'Clã',
            'agradecer'                 => 'Clã',
        ];

        $culturaContexto = [
            'Clã' => [
                'ideal' => 'Engajamento, retenção e fortalecimento de equipes.',
                'evitar' => 'Pode gerar lentidão e falta de cobrança clara.'
            ],
            'Adhocracia' => [
                'ideal' => 'Inovação, adaptação rápida e crescimento em mercados ágeis.',
                'evitar' => 'Risco de desorganização se não houver estrutura mínima.'
            ],
            'Hierárquica' => [
                'ideal' => 'Escalar com controle, setores regulados e padronização.',
                'evitar' => 'Pode engessar a cultura e reduzir autonomia e engajamento.'
            ],
            'Mercado' => [
                'ideal' => 'Competição, foco em resultado e metas agressivas.',
                'evitar' => 'Pode gerar clima tóxico e cultura baseada apenas em números.'
            ]
        ];

        $diagnostics = Diagnostic::with(['questions.options', 'campaigns', 'plain'])->get();
        
        $diagnosticsFiltered = $diagnostics->filter(function ($diagnostic) use ($authUser) {
            return $authUser->role === 'superadmin' 
                || $diagnostic->plain_id === ($authUser->tenant->plain_id ?? null);
        })->map(function ($diagnostic) use ($authUser, $openAIService, $mapaQuadrantes) {
            $categoryScores = []; 
            $allScores = [];      

            $answersGrouped = $diagnostic->questions->map(function ($question) use ($diagnostic, $authUser, $openAIService, &$categoryScores, &$allScores) {
                $answers = Answer::where('diagnostic_id', $diagnostic->id)
                    ->where('question_id', $question->id)
                    ->where('tenant_id', $authUser->tenant_id)
                    ->get();

                if ($question->type === 'fechada') {
                    $avg = $answers->avg('note');

                    if (!is_null($avg)) {
                        $categoryScores[$question->category][] = $avg;
                        $allScores[] = $avg;                        
                    }

                    return [
                        'question' => $question,
                        'average' => $avg,
                        'answers' => $answers->pluck('note')->toArray(),
                        'average_open_sentiment' => null
                    ];
                }

                $analyzed = [];
                foreach ($answers as $ans) {
                    if (!$ans->analyzed) {
                        $score = $openAIService->gradeAnswer($question->text, $ans->text);
                        $ans->score = $score;
                        $ans->analyzed = true;
                        $ans->save();
                    } else {
                        $score = $ans->score;
                    }

                    if (!is_null($score)) {
                        $analyzed[] = ['text' => trim($ans->text), 'score' => $score];
                    }
                }

                $avgScore = collect($analyzed)->avg('score');

                if (!is_null($avgScore)) {
                    $categoryScores[$question->category][] = $avgScore;
                    $allScores[] = $avgScore;
                }

                return [
                    'question' => $question,
                    'average' => null,
                    'answers' => $analyzed,
                    'average_open_sentiment' => $avgScore
                ];
            });

            $categoryAverages = collect($categoryScores)->map(fn($scores) => round(collect($scores)->avg(), 2))->filter();
            $overallAverage = round(collect($allScores)->avg(), 2);
            
            $culturaScoresGeral = [
                'Clã' => [], 'Adhocracia' => [], 'Hierárquica' => [], 'Mercado' => []
            ];

            foreach ($categoryAverages as $categoria => $media) {
                $quadrante = $mapaQuadrantes[$categoria] ?? null;
                if ($quadrante) {
                    $culturaScoresGeral[$quadrante][] = $media;
                }
            }

            $culturaAverages = collect($culturaScoresGeral)
                ->map(fn($scores) => count($scores) ? round(collect($scores)->avg(), 2) : 0);

            $userRoles = ['admin', 'colaborador'];
            $culturaResultados = [];

            foreach ($userRoles as $role) {
                $respostasPorRole = Answer::where('diagnostic_id', $diagnostic->id)
                    ->where('tenant_id', $authUser->tenant_id)
                    ->whereHas('user', fn($q) => $q->where('role', $role))
                    ->get()
                    ->groupBy('question_id');

                $pontuacoesPorCategoria = [];

                foreach ($respostasPorRole as $questionId => $answers) {
                    $question = $diagnostic->questions->firstWhere('id', $questionId);
                    if (!$question) continue;

                    $avg = $question->type === 'fechada'
                        ? $answers->avg('note')
                        : $answers->avg('score');

                    if (!is_null($avg)) {
                        $pontuacoesPorCategoria[$question->category][] = $avg;
                    }
                }

                $mediasCategoria = collect($pontuacoesPorCategoria)
                    ->map(fn($v) => round(collect($v)->avg(), 2))
                    ->filter();

                $culturaScores = [
                    'Clã' => [], 'Adhocracia' => [], 'Hierárquica' => [], 'Mercado' => []
                ];

                foreach ($mediasCategoria as $categoria => $media) {
                    $quadrante = $mapaQuadrantes[$categoria] ?? null; 
                    if ($quadrante) {
                        $culturaScores[$quadrante][] = $media;
                    }
                }

                $culturaMedias = collect($culturaScores)
                    ->map(fn($scores) => count($scores) ? round(collect($scores)->avg(), 2) : 0);

                $ordenadas = $culturaMedias->sortDesc();
                $maisPresentes = $ordenadas->take(2);
                $menosPresentes = $ordenadas->reverse()->take(2);

                $culturaResultados[$role] = [
                    'medias' => $culturaMedias,
                    'predominantes' => $maisPresentes,
                    'ausentes' => $menosPresentes
                ];
            }

            return [
                'diagnostic' => $diagnostic,
                'hasAnswered' => Answer::where('diagnostic_id', $diagnostic->id)
                    ->where('user_id', $authUser->id)->exists(),
                'hasQuestions' => $diagnostic->questions->isNotEmpty(),
                'answersGrouped' => $answersGrouped,
                'categoryAverages' => $categoryAverages,
                'culturaAverages' => $culturaAverages, 
                'overallAverage' => $overallAverage,
                'analisePorRole' => $culturaResultados
            ];
        });


        return view ('diagnostic.index', compact('authUser', 'diagnosticsFiltered', 'categoriaFormatada', 'culturaContexto'));
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
                $cleanText = $answer['text'];  

                $cleanText = preg_replace('/^\s+|\s+$/u', '', $cleanText);

                $cleanText = preg_replace('/[ \t]+/', ' ', $cleanText);   
                
                $cleanText = preg_replace("/\n{3,}/", "\n\n", $cleanText);

                Answer::updateOrCreate(
                    [
                        'diagnostic_id' => $diagnostic->id,
                        'question_id'   => $questionId,
                        'user_id'       => $authUser->id,
                        'tenant_id'     => $authUser->tenant->id
                    ],
                    [
                        'note'          => null,
                        'text'          => $cleanText
                    ]
                );
            }
        }

        return redirect()->route('diagnostico.index')->with('success', 'Diagnóstico respondido com sucesso.');
    }
}

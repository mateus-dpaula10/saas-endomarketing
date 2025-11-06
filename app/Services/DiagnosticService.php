<?php

namespace App\Services;

use App\Models\{Diagnostic, Answer, DiagnosticQuadrantAnalysis, ComparativeRole};
use Illuminate\Support\Collection;

class DiagnosticService
{
    public static function categoriaFormatada(): array
    {
        return [
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
    }

    public static function mapaQuadrantes(): array
    {
        return [
            'identidade_proposito'      => 'Clã',
            'valores_comportamentos'    => 'Mercado',
            'ambiente_clima'            => 'Clã',
            'reconhecimento_celebracao' => 'Clã',
            'diversidade_pertencimento' => 'Adhocracia',
            'comunicacao_lideranca'     => 'Adhocracia',
            'processos_praticas'        => 'Hierárquica',
            'aspiracoes_futuro'         => 'Mercado'
        ];
    }

    public static function culturaContexto(): array
    {
        return [
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
    }

    public function prepareDiagnosticData(Diagnostic $diagnostic, $authUser, OpenAIService $openAIService): array
    {
        $mapaQuadrantes = $this->mapaQuadrantes();

        $categoryScores = []; 
        $allScores = [];      

        $answersGrouped = $diagnostic->questions->map(function ($question) use ($diagnostic, $authUser, $openAIService, &$categoryScores, &$allScores) {
            $answers = Answer::where('diagnostic_id', $diagnostic->id)
                ->where('question_id', $question->id)
                ->where('tenant_id', $authUser->tenant_id)
                ->get();

            if ($question->type === 'fechada') {
                $answersText = [];
                foreach ($answers as $ans) {
                    $selectedOption = collect($question->options)->firstWhere('weight', $ans->note);
                    $answersText[] = $selectedOption['text'] ?? "Opção não encontrada ({$ans->note})";
                }

                $avg = $answers->avg('note');
                if (!is_null($avg)) {
                    $categoryScores[$question->category][] = $avg;
                    $allScores[] = $avg;                        
                }

                return [
                    'question' => $question,
                    'average' => $avg,
                    'answers' => $answersText,
                    'average_open_sentiment' => null
                ];
            }

            $analyzed = [];
            foreach ($answers as $ans) {
                if (!$ans->analyzed) {
                    $score = $openAIService->gradeAnswer($question->text, $ans->text);
                    $ans->update(['score' => $score, 'analyzed' => true]);
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

        $categoryAverages = collect($categoryScores)
            ->map(fn($scores) => round(collect($scores)->avg(), 2))
            ->filter();

        $overallAverage = round(collect($allScores)->avg(), 2);
        
        $culturaScoresGeral = ['Clã' => [], 'Adhocracia' => [], 'Hierárquica' => [], 'Mercado' => []];
        foreach ($categoryAverages as $categoria => $media) {
            $quadrante = $mapaQuadrantes[$categoria] ?? null;
            if ($quadrante) $culturaScoresGeral[$quadrante][] = $media;
        }

        $culturaAverages = collect($culturaScoresGeral)
            ->map(fn($scores) => count($scores) ? round(collect($scores)->avg(), 2) : 0);

        $userRoles = ['admin', 'user'];
        $culturaResultados = [];
        $resumoPorRole = [];
        $resumoGeral = null;

        foreach ($userRoles as $role) {
            $analysis = DiagnosticQuadrantAnalysis::with('comparativeRoles')
                ->where('diagnostic_id', $diagnostic->id)
                ->where('tenant_id', $authUser->tenant_id)
                ->where('role', $role)
                ->first();
            
            $needsAnalysis = !$analysis && $this->hasAnswersChanged($diagnostic, $role, $authUser->tenant_id);

            if ($analysis && !$needsAnalysis) {
                $culturaResultados[$role] = [
                    'medias'            => $analysis->medias,
                    'classificacao'     => $analysis->classificacao,
                    'sinais'            => $analysis->sinais,
                    'comparativoRoles'  => $analysis->comparativeRoles,
                ];
                if (!empty($analysis->resumo)) {
                    $resumoPorRole[$role] = $analysis->resumo;
                }
                continue;
            }

            $respostasPorRole = Answer::where('diagnostic_id', $diagnostic->id)
                ->where('tenant_id', $authUser->tenant_id)
                ->whereHas('user', fn($q) => $q->where('role', $role))
                ->get();

            if ($respostasPorRole->isEmpty()) continue;

            $respostasPorRole = $respostasPorRole->groupBy('question_id');
            $pontuacoesPorCategoria = [];
            $respostasAbertas = [];

            foreach ($respostasPorRole as $questionId => $answers) {
                $question = $diagnostic->questions->firstWhere('id', $questionId);
                if (!$question) continue;

                $avg = $question->type === 'fechada' ? $answers->avg('note') : $answers->avg('score');
                if (!is_null($avg)) $pontuacoesPorCategoria[$question->category][] = $avg;

                if ($question->type === 'aberta') {
                    foreach ($answers as $ans) {
                        $respostasAbertas[] = [
                            'question' => $question->text,
                            'answer' => $ans->text,
                            'category' => $question->category,
                        ];
                    }
                }
            }

            $culturaScores = ['Clã' => [], 'Adhocracia' => [], 'Hierárquica' => [], 'Mercado' => []];
            foreach ($pontuacoesPorCategoria as $categoria => $medias) {
                $quadrante = $mapaQuadrantes[$categoria] ?? null;
                if ($quadrante) {
                    $culturaScores[$quadrante] = array_merge($culturaScores[$quadrante], $medias);
                }
            }

            $culturaMedias = collect($culturaScores)
                ->map(function ($scores) {
                    if (empty($scores)) return 0.0;
                    $avg = collect($scores)->avg();
                    return is_numeric($avg) ? round((float) $avg, 2) : 0.0;
                });
                
            $culturaMediasOrdenadas = $culturaMedias->sortDesc()->values();
            $culturaMediasOrdenadasAssoc = $culturaMedias->sortDesc();

            $quadrantesOrdenados = $culturaMediasOrdenadasAssoc->keys()->toArray();

            $quadranteClassificado = [
                'predominante' => [$quadrantesOrdenados[0] ?? null],
                'secundario'   => [$quadrantesOrdenados[1] ?? null],
                'fraco'        => [$quadrantesOrdenados[2] ?? null],
                'ausente'      => array_values(array_filter(array_slice($quadrantesOrdenados, 3)))
            ];
            
            $respostasPorQuadrante = [];            
            foreach ($respostasAbertas as $resp) {
                $category = $resp['category'] ?? null;
                $quadrante = $mapaQuadrantes[$category] ?? null;
                if ($quadrante) $respostasPorQuadrante[$quadrante][] = $resp['answer'];
            }            

            $sinaisPorQuadrante = [];
            foreach ($respostasPorQuadrante as $quadrante => $resps) {
                $sinaisPorQuadrante[$quadrante] = $openAIService->analyzeQuadrant($quadrante, $resps);
            }

            $textoParaIA = implode("\n\n", $sinaisPorQuadrante);
            $resumoRole = $openAIService->analyzeSummaryFromSinais($role, $textoParaIA);
            $resumoPorRole[$role] = $resumoRole;

            $analysis = DiagnosticQuadrantAnalysis::updateOrCreate(
                [
                    'diagnostic_id' => $diagnostic->id,
                    'tenant_id' => $authUser->tenant_id,
                    'role' => $role
                ],
                [
                    'medias' => is_array($culturaMedias) ? $culturaMedias : (method_exists($culturaMedias, 'toArray') ? $culturaMedias->toArray() : []),
                    'classificacao' => $quadranteClassificado ?? [],
                    'sinais' => $sinaisPorQuadrante ?? [],
                    'resumo' => $resumoRole ?? '',
                    'resumo_geral' => null
                ]
            );

            $culturaResultados[$role] = [
                'medias' => $culturaMedias,
                'classificacao' => $quadranteClassificado,
                'sinais' => $sinaisPorQuadrante
            ];
        }

        $comparativo = null;

        if (!empty($resumoPorRole['user']) && !empty($resumoPorRole['admin'])) {
            $analysisBase = DiagnosticQuadrantAnalysis::firstOrCreate([
                'diagnostic_id' => $diagnostic->id,
                'tenant_id'     => $authUser->tenant_id,
                'role'          => 'geral'
            ], [
                'medias'        => [],
                'classificacao' => [],
                'sinais'        => [],
                'resumo'        => '',
                'resumo_geral'  => ''
            ]);

            $existingComparatives = ComparativeRole::where('diagnostic_quadrant_analysis_id', $analysisBase->id)->get();

            $lastAnswer = Answer::where('diagnostic_id', $diagnostic->id)
                ->where('tenant_id', $authUser->tenant_id)
                ->whereHas('user', fn($q) => $q->whereIn('role', ['user', 'admin']))
                ->latest('updated_at')
                ->first();
                
            $comparativeUpdatedAt = $analysisBase->updated_at;

            $shouldRegenerate = false;

            if ($existingComparatives->isEmpty()) {
                $shouldRegenerate = true;
            }

            if (!$shouldRegenerate && $lastAnswer) {
                if (is_null($comparativeUpdatedAt) || $lastAnswer->updated_at > $comparativeUpdatedAt) {
                    $shouldRegenerate = true;
                }
            }

            $culturaDominanteUser  = null;
            $culturaDominanteAdmin = null;

            if (!empty($culturaResultados['user']['classificacao']['predominante'][0])) {
                $culturaDominanteUser = $culturaResultados['user']['classificacao']['predominante'][0];
            }

            if (!empty($culturaResultados['admin']['classificacao']['predominante'][0])) {
                $culturaDominanteAdmin = $culturaResultados['admin']['classificacao']['predominante'][0];
            }

            if ($shouldRegenerate) {
                $comparativoGerado = $openAIService->analyzeComparativeTable(
                    $resumoPorRole['user'],
                    $resumoPorRole['admin'],
                    $culturaDominanteUser,
                    $culturaDominanteAdmin
                );

                if (!empty($comparativoGerado) && is_array($comparativoGerado)) {
                    \DB::transaction(function () use ($analysisBase, $comparativoGerado) {
                        ComparativeRole::where('diagnostic_quadrant_analysis_id', $analysisBase->id)->delete();

                        foreach ($comparativoGerado as $item) {
                            ComparativeRole::create([
                                'diagnostic_quadrant_analysis_id' => $analysisBase->id,
                                'elemento'                        => $item['elemento'] ?? null,
                                'colaboradores'                   => $item['colaboradores'] ?? null,
                                'gestao'                          => $item['gestao'] ?? null
                            ]);
                        }

                        $analysisBase->touch();
                    });
                }
            }

            $comparativo = ComparativeRole::where('diagnostic_quadrant_analysis_id', $analysisBase->id)
                ->orderBy('id')
                ->get();
        }

        $existingGeral = DiagnosticQuadrantAnalysis::where('diagnostic_id', $diagnostic->id)
            ->where('tenant_id', $authUser->tenant_id)
            ->where('role', 'geral')
            ->first();

        $lastAnswer = Answer::where('diagnostic_id', $diagnostic->id)
            ->where('tenant_id', $authUser->tenant_id)
            ->whereHas('user', fn($q) => $q->whereIn('role', ['user', 'admin']))
            ->latest('updated_at')
            ->first();

        $shouldRegenerateResumoGeral = false;

        if ($existingGeral === null) {
            $shouldRegenerateResumoGeral = true;
        }

        if (!$shouldRegenerateResumoGeral && $lastAnswer) {
            if (is_null($existingGeral->updated_at) || $lastAnswer->updated_at > $existingGeral->updated_at) {
                $shouldRegenerateResumoGeral = true;
            }
        }

        if ($shouldRegenerateResumoGeral && !empty($resumoPorRole)) {
            $textoGeral = '';
            foreach ($resumoPorRole as $role => $resumo) {
                $textoGeral .= strtoupper($role) . ":\n{$resumo}\n\n";
            }

            $resumoGeral = $openAIService->analyzeSummaryFromSinais('geral', $textoGeral);

            if ($existingGeral) {
                $existingGeral->update(['resumo_geral' => $resumoGeral]);
                $existingGeral->touch();
            } else {
                DiagnosticQuadrantAnalysis::create([
                    'diagnostic_id' => $diagnostic->id,
                    'tenant_id'     => $authUser->tenant_id,
                    'role'          => 'geral',
                    'resumo_geral'  => $resumoGeral,
                    'medias'        => [],
                    'classificacao' => [],
                    'sinais'        => [],
                ]);
            }
        } else {
            $resumoGeral = $existingGeral->resumo_geral ?? null;
        }

        return [
            'diagnostic'       => $diagnostic,
            'hasAnswered'      => Answer::where('diagnostic_id', $diagnostic->id)
                ->where('user_id', $authUser->id)->exists(),
            'hasQuestions'     => $diagnostic->questions->isNotEmpty(),
            'answersGrouped'   => $answersGrouped,
            'categoryAverages' => $categoryAverages,
            'culturaAverages'  => $culturaAverages, 
            'overallAverage'   => $overallAverage,
            'analisePorRole'   => $culturaResultados,
            'resumoPorRole'    => $resumoPorRole,
            'resumoGeral'      => $resumoGeral,
            'comparativoRoles' => $comparativo ?? []
        ];
    }

    public function hasAnswersChanged(Diagnostic $diagnostic, string $role): bool
    {
        $lastAnswer = Answer::where('diagnostic_id', $diagnostic->id)
            ->whereHas('user', fn($q) => $q->where('role', $role))
            ->latest('updated_at')
            ->first();

        $lastAnalysis = DiagnosticQuadrantAnalysis::where('diagnostic_id', $diagnostic->id)
            ->where('role', $role)
            ->latest('updated_at')
            ->first();

        if (!$lastAnalysis) return true;
        if (!$lastAnswer) return false;

        return $lastAnswer->updated_at > $lastAnalysis->updated_at;
    }
}
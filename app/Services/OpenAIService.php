<?php

namespace App\Services;
use Illuminate\Support\Facades\Http;

class OpenAIService
{
    protected $apiKey;
    protected $apiUrl;

    public function __construct()
    {
        $this->apiKey = env('OPENAI_API_KEY');
        $this->apiUrl = 'https://api.openai.com/v1/chat/completions';
    }

    public function gradeAnswer(string $question, string $answer): float
    {
        try {
            $prompt = "Você é um avaliador especializado em cultura organizacional e comunicação interna. 
                Dada a pergunta e a resposta abaixo, atribua uma nota de 0 a 5 baseada em quanto a resposta **avalia e reflete a cultura e a comunicação interna da empresa**, considerando:

                - 5: resposta totalmente relevante, demonstra excelente compreensão do tema, alinhada à pergunta;
                - 4: resposta boa, relevante e parcialmente completa;
                - 3: resposta razoável, parcialmente alinhada, mas superficial;
                - 2: pouco alinhada ou irrelevante;
                - 1: quase nada alinhada à pergunta;
                - 0: totalmente irrelevante ou negativa sem sentido para o contexto.

                Pergunta: {$question}
                Resposta: {$answer}

                **Retorne apenas o número da nota (0 a 5), sem texto adicional.**"
            ;

            $response = Http::withToken($this->apiKey)
                ->post($this->apiUrl, [
                    'model' => 'gpt-3.5-turbo',
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt]
                    ],
                    'temperature' => 0
                ]);

            $result = $response->json();

            $text = $result['choices'][0]['message']['content'] ?? '0';
            preg_match('/[0-5]/', $text, $matches);
            $score = isset($matches[0]) ? floatval($matches[0]) : 0;

            return max(0, min(5, $score));
        } catch (\Exception $e) {
            return 0;
        }
    }

    public function analyzeQuadrantContext(array $openAnswers, array $quadrantScores): array
    {
        try {
            $concatenated = implode("\n\n", array_map(function ($r) {
                return "Pergunta: {$r['question']}\nResposta: {$r['answer']}";
            }, $openAnswers));

            $prompt = "
                Você é um consultor de cultura organizacional.
                A seguir estão respostas abertas de colaboradores e líderes.

                Também informo a pontuação média (0 a 5) de cada tipo de cultura organizacional:

                Clã: " . ($quadrantScores['clã'] ?? 'N/A') . "
                Adhocracia: " . ($quadrantScores['adhocracia'] ?? 'N/A') . "
                Hierárquica: " . ($quadrantScores['hierárquica'] ?? 'N/A') . "
                Mercado: " . ($quadrantScores['mercado'] ?? 'N/A') . "

                Analise o texto das respostas e gere um resumo analítico **para cada quadrante** contendo:
                - **Aspectos positivos** observados nas respostas
                - **Fragilidades ou desafios**
                - Interpretação alinhada à pontuação média do quadrante (mais forte ou fraco)
                - Tom neutro e consultivo

                Respostas:
                {$concatenated}

                Retorne no formato:
                Clã: [texto curto 2–4 linhas]
                Adhocracia: [texto curto 2–4 linhas]
                Hierárquica: [texto curto 2–4 linhas]
                Mercado: [texto curto 2–4 linhas]
            ";

            $response = Http::withToken($this->apiKey)
                ->post($this->apiUrl, [
                    'model' => 'gpt-3.5-turbo',
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => 0.7,
                ]);

            $text = trim($response->json()['choices'][0]['message']['content'] ?? '');

            $analysis = [];
            foreach (['Clã', 'Adhocracia', 'Hierárquica', 'Mercado'] as $quadrante) {
                if (preg_match("/{$quadrante}:(.*?)(?=(Clã|Adhocracia|Hierárquica|Mercado|$))/s", $text, $matches)) {
                    $analysis[strtolower($quadrante)] = trim($matches[1]);
                }
            }

            return $analysis;

        } catch (\Exception $e) {
            return [];
        }
    }

}

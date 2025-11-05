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
            $prompt = "
                Você é um avaliador especializado em cultura e clima organizacional.
                Sua tarefa: analisar a pergunta e a resposta e atribuir UMA NOTA (0–5) que represente o quanto a resposta efetivamente atende ao que foi perguntado **considerando a direção esperada**.

                Passos que o modelo deve seguir:
                1. Identifique a intenção da pergunta: ela pede uma avaliação (ex.: 'Qual o nível de comprometimento?', 'O clima é bom?'), uma descrição (ex.: 'Como é trabalhar aqui?') ou um exemplo/ocorrência (ex.: 'Conte uma situação...').
                2. Se a pergunta for avaliativa (pede nível/qualidade/sentimento), interprete a resposta como positiva/negativa/neutra. NOTA ALINHADA:
                - resposta claramente positiva/indica presença do aspecto → nota alta (4–5);
                - resposta moderada/ambígua → nota média (2–3);
                - resposta negativa/indica ausência do aspecto → nota baixa (0–1).
                3. Se a pergunta for descritiva ou pedir exemplos, avalie se a resposta traz informação útil e específica:
                - detalhada e exemplifica bem → 4–5;
                - genérica/parcial → 2–3;
                - irrelevante/fora do escopo → 0–1.
                4. Considere palavras-chave e tom (ex.: 'colaborativo', 'sempre', 'nunca', 'muito', 'pouco', 'não existem', 'orgulhoso', 'insatisfeito') para inferir presença/ausência.
                5. Retorne **apenas** o número inteiro 0,1,2,3,4 ou 5 e nada mais.

                Exemplos curtos (não retorne estes exemplos, são guia interno):
                - Pergunta: 'Qual o nível de comprometimento das pessoas?'
                Resposta: 'As pessoas não cumprem as tarefas e muitos faltam' → nota baixa (0–1).
                Resposta: 'A maioria se envolve e se esforça além do horário' → nota alta (4–5).
                - Pergunta: 'Como é o clima aqui?'
                Resposta: 'Hostil, muita pressão e reclamações' → nota baixa.
                Resposta: 'acolhedor e colaborativo' → nota alta.

                Agora avalie:

                Pergunta: {$question}
                Resposta: {$answer}

                **Resposta esperada: apenas um número inteiro (0 a 5).**
            ";

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

            if (preg_match('/\b([0-5])\b/', $text, $m)) {
                $score = intval($m[1]);
            } else {
                $score = 0;
            }

            return max(0, min(5, $score));
        } catch (\Exception $e) {
            return 0;
        }
    }

    public function analyzeQuadrant(string $quadrante, array $respostas): string
    {
        $respostasTexto = implode("\n- ", $respostas);

        $prompt = "
            Você é um especialista em cultura e clima organizacional.
            Analise as respostas abaixo relacionadas ao quadrante '{$quadrante}'.
            
            Gere um texto corrido e claro, de até 120 palavras, que:
            - Destaque de forma natural os **sinais de presença** e **sinais de ausência** desse quadrante.
            - Evite repetições, listas ou frases iniciadas por 'Presença:' ou 'Ausência:'.
            - Use linguagem fluida e profissional, com frases completas e bem conectadas.
            - Deixe claro, dentro do texto, quais comportamentos ou percepções demonstram a presença do quadrante
            e quais evidenciam sua ausência.
            - Mantenha o texto interpretativo, sem bullets, listas ou enumerações.
            - Evite frases genéricas e repetições. Seja objetivo e interpretativo.
            
            Respostas:
            - {$respostasTexto}
        ";

        $response = Http::withToken($this->apiKey)
            ->post($this->apiUrl, [
                'model' => 'gpt-3.5-turbo',
                'messages' => [['role' => 'user', 'content' => $prompt]],
                'temperature' => 0.3,
            ]);

        $result = $response->json();

        return $result['choices'][0]['message']['content'] ?? '';
    }
    
    public function analyzeSummaryFromSinais(string $role, string $texto): string
    {
        $prompt = "
            Você é um especialista em cultura e clima organizacional.
            Analise o seguinte texto de sinais para o role '{$role}':

            Objetivo: gerar um resumo qualitativo consolidado, destacando forças, pontos de atenção e recomendações gerais da cultura organizacional.
            
            Instruções:
            - Resuma todos os sinais juntos, sem separar por quadrante.
            - Seja objetivo e claro.
            - Limite o resumo a aproximadamente 150 palavras.
            - Concentre-se em comportamentos, práticas ou percepções que impactam o clima organizacional.
            
            Texto de sinais:
            {$texto}
        ";

        $response = Http::withToken($this->apiKey)
            ->post($this->apiUrl, [
                'model' => 'gpt-3.5-turbo',
                'messages' => [['role' => 'user', 'content' => $prompt]],
                'temperature' => 0.5,
                'max_tokens' => 600,
            ]);

        $result = $response->json();

        return $result['choices'][0]['message']['content'] ?? '';
    }

    public function analyzeComparativeTable(string $resumoUser, string $resumoAdmin): array
    {
        $prompt = "
            Você é um analista de cultura organizacional.
            Compare os dois resumos abaixo — um dos colaboradores e outro da gestão — 
            e produza uma tabela comparativa com os seguintes elementos fixos:
            1. Cultura predominante
            2. Reconhecimento
            3. Comunicação
            4. Liderança
            5. Comprometimento
            6. Aspiração

            Gere o resultado **somente em JSON**, no formato:
            [
                {
                    \"elemento\": \"Cultura predominante\",
                    \"colaboradores\": \"Hierárquica + traços de Mercado\",
                    \"gestao\": \"Clã + Hierárquica\"
                },
                ...
            ]

            Seja conciso, objetivo e utilize frases curtas e comparativas.

            ---
            RESUMO DOS COLABORADORES:
            {$resumoUser}

            ---
            RESUMO DA GESTÃO:
            {$resumoAdmin}
        ";

        $response = Http::withToken($this->apiKey)->post($this->apiUrl, [
            'model' => 'gpt-3.5-turbo',
            'messages' => [['role' => 'user', 'content' => $prompt]],
            'temperature' => 0.4,
            'max_tokens' => 1200,
        ]);

        $content = trim($response->json('choices.0.message.content', ''));
        $decoded = json_decode($content, true);

        $elementosFixos = [
            'Cultura predominante',
            'Reconhecimento',
            'Comunicação',
            'Liderança',
            'Comprometimento',
            'Aspiração'
        ];

        $final = [];
        foreach ($elementosFixos as $el) {
            $match = collect($decoded ?? [])->firstWhere('elemento', $el);

            $final[] = [
                'elemento' => $el,
                'colaboradores' => $match['colaboradores'] ?? 'Sem dados',
                'gestao' => $match['gestao'] ?? 'Sem dados',
            ];
        }

        return $final;
    }
}
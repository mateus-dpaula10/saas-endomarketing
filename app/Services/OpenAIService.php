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
            
            Objetivo: identificar sinais que indiquem a **presença, parcialidade ou ausência** do quadrante.  
            Para cada sinal, indique claramente se ele representa **presença** ou **ausência** do quadrante.

            Instruções:
            - Liste apenas os sinais mais relevantes.
            - Cada sinal deve começar com 'Presença:' ou 'Ausência:' e **ficar em uma linha separada**.
            - Use apenas quebras de linha (\n), sem bullets ou números.
            - Agrupe ideias semelhantes em uma única linha.
            - Limite toda a análise a **no máximo 100 palavras**.
            - Evite repetições e exemplos longos.
            - Concentre-se em comportamentos, práticas ou percepções que caracterizam o quadrante.
            
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
}
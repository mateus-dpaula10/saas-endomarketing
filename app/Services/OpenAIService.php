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
                Sua tarefa é analisar a pergunta e a resposta e atribuir UMA NOTA (0–5) que represente a **qualidade cultural e de clima organizacional** revelada pela resposta.

                Critério principal: avalie se a resposta indica práticas, valores e comportamentos **positivos e saudáveis** ou **negativos e problemáticos** para o ambiente organizacional.

                ---

                ### Diretrizes de avaliação:

                1. **Entenda o contexto da pergunta**
                - Perguntas podem tratar de propósito, valores, reconhecimento, comunicação, liderança, engajamento, colaboração, inovação, etc.
                - Interprete sempre o que a resposta revela sobre o funcionamento da cultura da empresa (e não apenas se ela responde à pergunta).

                2. **Atribua a nota considerando o impacto cultural:**
                - **5:** resposta demonstra uma cultura muito saudável (propósito claro, reconhecimento, colaboração, confiança, aprendizado, diversidade, engajamento).
                - **4:** resposta positiva, mas com pequenas limitações.
                - **3:** resposta neutra, morna, pouco reveladora ou ambígua.
                - **2:** resposta revela sinais de fragilidade cultural ou clima problemático.
                - **1:** resposta claramente negativa (falta de propósito, desmotivação, conflitos, ausência de valores).
                - **0:** resposta indica disfunção grave ou ausência total de práticas positivas.

                3. **Considere o tom e palavras-chave:**
                - Positivas: 'colaborativo', 'propósito claro', 'valorização', 'aberto', 'apoio', 'reconhecido', 'aprendizado', 'inovação', 'orgulho'.
                - Negativas: 'pressão', 'desmotivação', 'não existe', 'só lucro', 'falta de comunicação', 'ninguém ouve', 'competição', 'desigualdade', 'não sabemos'.

                4. **Não avalie a coerência técnica, mas o conteúdo cultural.**
                Mesmo que a resposta esteja curta ou vaga, avalie o sentido cultural do que foi dito.

                5. Retorne **apenas o número inteiro** (0–5) e nada mais.

                ---

                ### Exemplo de referência (não retorne estes exemplos):

                - Pergunta: 'Por que a empresa existe? Qual é o propósito que vai além do lucro?'
                - 'Não sei.' → 0
                - 'Acho que só para dar lucro mesmo.' → 1
                - 'Gerar empregos e lucro para todos.' → 3
                - 'Gerar impacto positivo na sociedade e desenvolver pessoas.' → 5

                - Pergunta: 'Como é o clima aqui?'
                - 'As pessoas se ajudam e celebram conquistas.' → 5
                - 'É tranquilo, mas há pressão em algumas áreas.' → 3
                - 'Ambiente hostil e competitivo.' → 1

                ---

                Agora avalie:

                Pergunta: {$question}
                Resposta: {$answer}

                **Retorne apenas o número inteiro (0–5).**
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

    public function analyzeComparativeTable(
        string $resumoUser, 
        string $resumoAdmin,
        ?string $culturaUser = null,
        ?string $culturaAdmin = null
    ): array
    {
        $prompt = "
            Você é um especialista de cultura e clima organizacional.

            Compare os dois resumos abaixo — um dos colaboradores e outro da gestão — 
            e produza uma tabela comparativa com os seguintes elementos fixos:

            1. Cultura predominante
            2. Reconhecimento
            3. Comunicação
            4. Liderança
            5. Comprometimento
            6. Aspiração

            **Regras importantes:**
            - A 'Cultura predominante' deve refletir EXATAMENTE as culturas informadas a seguir, sem inferências:
                - Colaboradores: {$culturaUser}
                - Gestão: {$culturaAdmin}
            - Os demais campos (Reconhecimento, Comunicação, etc.) devem ser resumidos comparativamente com base nos textos dos resumos.
            - Seja conciso e use frases curtas e comparativas.
            - Responda **somente com um JSON válido**, sem texto adicional e sem blocos de código.

            Exemplo de formato:
            [
                {
                    \"elemento\": \"Cultura predominante\",
                    \"colaboradores\": \"Mercado\",
                    \"gestao\": \"Clã\"
                },
                ...
            ]

            --- RESUMO DOS COLABORADORES:
            {$resumoUser}

            --- RESUMO DA GESTÃO:
            {$resumoAdmin}
        ";

        $response = Http::withToken($this->apiKey)->post($this->apiUrl, [
            'model' => 'gpt-3.5-turbo',
            'messages' => [['role' => 'user', 'content' => $prompt]],
            'temperature' => 0.4,
            'max_tokens' => 1200,
        ]);

        $content = trim($response->json('choices.0.message.content', ''));
        $content = preg_replace('/^```json|```$/m', '', $content);
        $content = trim($content);
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
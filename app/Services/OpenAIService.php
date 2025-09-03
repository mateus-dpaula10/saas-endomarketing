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
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Question;
use App\Models\Option;

class QuestionsCulturaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $questions = [
            // 1-2 Identidade e propósito
            [
                'text' => 'Por que a empresa existe? Qual é o propósito que vai além do lucro?',
                'category' => 'identidade_proposito',
                'type' => 'aberta',
                'diagnostic_type' => 'cultura'
            ],
            [
                'text' => 'Como vocês descreveriam a missão e visão na prática do dia a dia?',
                'category' => 'identidade_proposito',
                'type' => 'aberta',
                'diagnostic_type' => 'cultura'
            ],

            // 3-5 Valores e comportamentos
            [
                'text' => 'Quais comportamentos são mais valorizados aqui? Quais são inaceitáveis?',
                'category' => 'valores_comportamentos',
                'type' => 'aberta',
                'diagnostic_type' => 'cultura'
            ],
            [
                'text' => 'Se alguém novo perguntasse: “o que é preciso para ter sucesso nesta empresa?”, o que vocês responderiam?',
                'category' => 'valores_comportamentos',
                'type' => 'aberta',
                'diagnostic_type' => 'cultura'
            ],
            [
                'text' => 'Qual o nível de comprometimento das pessoas?',
                'category' => 'valores_comportamentos',
                'type' => 'fechada',
                'diagnostic_type' => 'cultura',
                'options' => [
                    ['text' => 'Muito alto', 'weight' => 5],
                    ['text' => 'Alto', 'weight' => 4],
                    ['text' => 'Médio', 'weight' => 3],
                    ['text' => 'Baixo', 'weight' => 2],
                    ['text' => 'Muito baixo', 'weight' => 1],
                ]
            ],

            // 6-9 Ambiente e clima
            [
                'text' => 'Como é trabalhar aqui? Como descreveriam o clima em poucas palavras?',
                'category' => 'ambiente_clima',
                'type' => 'aberta',
                'diagnostic_type' => 'cultura'
            ],
            [
                'text' => 'O que motiva as pessoas a ficarem? O que leva alguém a sair?',
                'category' => 'ambiente_clima',
                'type' => 'aberta',
                'diagnostic_type' => 'cultura'
            ],
            [
                'text' => 'Conte uma situação em que você sentiu orgulho de trabalhar aqui.',
                'category' => 'ambiente_clima',
                'type' => 'aberta',
                'diagnostic_type' => 'cultura'
            ],
            [
                'text' => 'O que acontece quando alguém erra nesta empresa?',
                'category' => 'ambiente_clima',
                'type' => 'fechada',
                'diagnostic_type' => 'cultura',
                'options' => [
                    ['text' => 'É tratado como aprendizado', 'weight' => 5],
                    ['text' => 'Recebe feedback e orientação', 'weight' => 4],
                    ['text' => 'Há repreensão formal', 'weight' => 2],
                    ['text' => 'É ignorado', 'weight' => 1],
                ]
            ],

            // 10-13 Comunicação e liderança
            [
                'text' => 'Quais canais de comunicação são úteis?',
                'category' => 'comunicacao_lideranca',
                'type' => 'aberta',
                'diagnostic_type' => 'cultura'
            ],
            [
                'text' => 'Como acontece a comunicação com a liderança em via de mão dupla?',
                'category' => 'comunicacao_lideranca',
                'type' => 'aberta',
                'diagnostic_type' => 'cultura'
            ],
            [
                'text' => 'Como os líderes se comunicam? Existe espaço para diálogo aberto?',
                'category' => 'comunicacao_lideranca',
                'type' => 'aberta',
                'diagnostic_type' => 'cultura'
            ],
            [
                'text' => 'Como o feedback é dado e recebido?',
                'category' => 'comunicacao_lideranca',
                'type' => 'aberta',
                'diagnostic_type' => 'cultura'
            ],

            // 14-15 Processos e práticas
            [
                'text' => 'Como são tomadas as decisões importantes?',
                'category' => 'processos_praticas',
                'type' => 'aberta',
                'diagnostic_type' => 'cultura'
            ],
            [
                'text' => 'Há coerência entre o que a liderança diz e o que faz?',
                'category' => 'processos_praticas',
                'type' => 'fechada',
                'diagnostic_type' => 'cultura',
                'options' => [
                    ['text' => 'Sempre', 'weight' => 5],
                    ['text' => 'Na maioria das vezes', 'weight' => 4],
                    ['text' => 'Às vezes', 'weight' => 3],
                    ['text' => 'Raramente', 'weight' => 2],
                    ['text' => 'Nunca', 'weight' => 1],
                ]
            ],

            // 16-17 Reconhecimento e celebração
            [
                'text' => 'Como as conquistas são celebradas? O que se comemora (resultados, aniversários, tempo de casa, etc.)?',
                'category' => 'reconhecimento_celebracao',
                'type' => 'aberta',
                'diagnostic_type' => 'cultura'
            ],
            [
                'text' => 'Como é reconhecido um trabalho bem feito?',
                'category' => 'reconhecimento_celebracao',
                'type' => 'aberta',
                'diagnostic_type' => 'cultura'
            ],

            // 18-19 Diversidade, inclusão e pertencimento
            [
                'text' => 'Quão confortável as pessoas se sentem em serem elas mesmas?',
                'category' => 'diversidade_pertencimento',
                'type' => 'fechada',
                'diagnostic_type' => 'cultura',
                'options' => [
                    ['text' => 'Muito confortável', 'weight' => 5],
                    ['text' => 'Confortável', 'weight' => 4],
                    ['text' => 'Neutro', 'weight' => 3],
                    ['text' => 'Desconfortável', 'weight' => 2],
                    ['text' => 'Muito desconfortável', 'weight' => 1],
                ]
            ],
            [
                'text' => 'Há práticas formais ou informais para promover diversidade?',
                'category' => 'diversidade_pertencimento',
                'type' => 'aberta',
                'diagnostic_type' => 'cultura'
            ],

            // 20-23 Aspirações e futuro
            [
                'text' => 'Que tipo de cultura vocês desejam ter daqui a 5 anos?',
                'category' => 'aspiracoes_futuro',
                'type' => 'aberta',
                'diagnostic_type' => 'cultura'
            ],
            [
                'text' => 'O que precisaria mudar para chegar lá?',
                'category' => 'aspiracoes_futuro',
                'type' => 'aberta',
                'diagnostic_type' => 'cultura'
            ],
            [
                'text' => 'Como melhorar o engajamento e motivação da equipe?',
                'category' => 'aspiracoes_futuro',
                'type' => 'aberta',
                'diagnostic_type' => 'cultura'
            ],
            [
                'text' => 'Quais oportunidades de desenvolvimento e crescimento são percebidas?',
                'category' => 'aspiracoes_futuro',
                'type' => 'aberta',
                'diagnostic_type' => 'cultura'
            ],
        ];

        foreach ($questions as $qData) {
            $options = $qData['options'] ?? null;
            unset($qData['options']);

            $question = Question::create($qData);

            if ($options) {
                foreach ($options as $opt) {
                    $opt['question_id'] = $question->id;
                    Option::create($opt);
                }
            }
        }
    }
}

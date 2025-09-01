<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Question;
use App\Models\Option;

class QuestionsComunicacaoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $questions = [
            // 1- Contratar
            [
                'text' => 'Como avaliamos a comunicação durante o processo de contratação?',
                'category' => 'contratar',
                'type' => 'fechada',
                'diagnostic_type' => 'comunicacao',
                'options' => [
                    ['text' => 'Excelente', 'weight' => 5],
                    ['text' => 'Boa', 'weight' => 4],
                    ['text' => 'Regular', 'weight' => 3],
                    ['text' => 'Ruim', 'weight' => 2],
                    ['text' => 'Muito ruim', 'weight' => 1],
                ]
            ],
            [
                'text' => 'Quais informações são mais importantes compartilhar com novos colaboradores?',
                'category' => 'contratar',
                'type' => 'aberta',
                'diagnostic_type' => 'comunicacao'
            ],

            // 2- Celebrar
            [
                'text' => 'Como comemoramos conquistas e marcos importantes?',
                'category' => 'celebrar',
                'type' => 'aberta',
                'diagnostic_type' => 'comunicacao'
            ],
            [
                'text' => 'As celebrações impactam a motivação da equipe?',
                'category' => 'celebrar',
                'type' => 'fechada',
                'diagnostic_type' => 'comunicacao',
                'options' => [
                    ['text' => 'Muito', 'weight' => 5],
                    ['text' => 'Moderadamente', 'weight' => 4],
                    ['text' => 'Pouco', 'weight' => 3],
                    ['text' => 'Quase nada', 'weight' => 2],
                    ['text' => 'Nada', 'weight' => 1],
                ]
            ],

            // 3- Compartilhar
            [
                'text' => 'Como a informação é compartilhada entre equipes?',
                'category' => 'compartilhar',
                'type' => 'aberta',
                'diagnostic_type' => 'comunicacao'
            ],
            [
                'text' => 'O compartilhamento de informações é eficiente?',
                'category' => 'compartilhar',
                'type' => 'fechada',
                'diagnostic_type' => 'comunicacao',
                'options' => [
                    ['text' => 'Sempre', 'weight' => 5],
                    ['text' => 'Na maioria das vezes', 'weight' => 4],
                    ['text' => 'Às vezes', 'weight' => 3],
                    ['text' => 'Raramente', 'weight' => 2],
                    ['text' => 'Nunca', 'weight' => 1],
                ]
            ],

            // 4- Inspirar
            [
                'text' => 'Como a liderança inspira a equipe através da comunicação?',
                'category' => 'inspirar',
                'type' => 'aberta',
                'diagnostic_type' => 'comunicacao'
            ],
            [
                'text' => 'A comunicação da liderança é motivadora?',
                'category' => 'inspirar',
                'type' => 'fechada',
                'diagnostic_type' => 'comunicacao',
                'options' => [
                    ['text' => 'Muito', 'weight' => 5],
                    ['text' => 'Moderadamente', 'weight' => 4],
                    ['text' => 'Pouco', 'weight' => 3],
                    ['text' => 'Quase nada', 'weight' => 2],
                    ['text' => 'Nada', 'weight' => 1],
                ]
            ],

            // 5- Falar
            [
                'text' => 'As pessoas se sentem à vontade para falar e expressar opiniões?',
                'category' => 'falar',
                'type' => 'fechada',
                'diagnostic_type' => 'comunicacao',
                'options' => [
                    ['text' => 'Sempre', 'weight' => 5],
                    ['text' => 'Na maioria das vezes', 'weight' => 4],
                    ['text' => 'Às vezes', 'weight' => 3],
                    ['text' => 'Raramente', 'weight' => 2],
                    ['text' => 'Nunca', 'weight' => 1],
                ]
            ],
            [
                'text' => 'Quais situações dificultam que as pessoas falem abertamente?',
                'category' => 'falar',
                'type' => 'aberta',
                'diagnostic_type' => 'comunicacao'
            ],

            // 6- Escutar
            [
                'text' => 'A liderança escuta efetivamente as sugestões da equipe?',
                'category' => 'escutar',
                'type' => 'fechada',
                'diagnostic_type' => 'comunicacao',
                'options' => [
                    ['text' => 'Sempre', 'weight' => 5],
                    ['text' => 'Na maioria das vezes', 'weight' => 4],
                    ['text' => 'Às vezes', 'weight' => 3],
                    ['text' => 'Raramente', 'weight' => 2],
                    ['text' => 'Nunca', 'weight' => 1],
                ]
            ],
            [
                'text' => 'Como os feedbacks são recebidos e interpretados?',
                'category' => 'escutar',
                'type' => 'aberta',
                'diagnostic_type' => 'comunicacao'
            ],

            // 7- Cuidar
            [
                'text' => 'De que forma a comunicação demonstra cuidado com as pessoas?',
                'category' => 'cuidar',
                'type' => 'aberta',
                'diagnostic_type' => 'comunicacao'
            ],
            [
                'text' => 'As ações de cuidado são percebidas pela equipe?',
                'category' => 'cuidar',
                'type' => 'fechada',
                'diagnostic_type' => 'comunicacao',
                'options' => [
                    ['text' => 'Sempre', 'weight' => 5],
                    ['text' => 'Na maioria das vezes', 'weight' => 4],
                    ['text' => 'Às vezes', 'weight' => 3],
                    ['text' => 'Raramente', 'weight' => 2],
                    ['text' => 'Nunca', 'weight' => 1],
                ]
            ],

            // 8- Desenvolver
            [
                'text' => 'Como a comunicação contribui para o desenvolvimento das pessoas?',
                'category' => 'desenvolver',
                'type' => 'aberta',
                'diagnostic_type' => 'comunicacao'
            ],
            [
                'text' => 'As oportunidades de crescimento são claras e bem comunicadas?',
                'category' => 'desenvolver',
                'type' => 'fechada',
                'diagnostic_type' => 'comunicacao',
                'options' => [
                    ['text' => 'Sempre', 'weight' => 5],
                    ['text' => 'Na maioria das vezes', 'weight' => 4],
                    ['text' => 'Às vezes', 'weight' => 3],
                    ['text' => 'Raramente', 'weight' => 2],
                    ['text' => 'Nunca', 'weight' => 1],
                ]
            ],

            // 9- Agradecer
            [
                'text' => 'Como expressamos gratidão e reconhecimento através da comunicação?',
                'category' => 'agradecer',
                'type' => 'aberta',
                'diagnostic_type' => 'comunicacao'
            ],
            [
                'text' => 'O agradecimento é percebido e valorizado pela equipe?',
                'category' => 'agradecer',
                'type' => 'fechada',
                'diagnostic_type' => 'comunicacao',
                'options' => [
                    ['text' => 'Sempre', 'weight' => 5],
                    ['text' => 'Na maioria das vezes', 'weight' => 4],
                    ['text' => 'Às vezes', 'weight' => 3],
                    ['text' => 'Raramente', 'weight' => 2],
                    ['text' => 'Nunca', 'weight' => 1],
                ]
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

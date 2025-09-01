<?php

namespace Database\Seeders;
use App\Models\Diagnostic;
use App\Models\Question;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DiagnosticQuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cultura = Diagnostic::firstOrCreate(['type' => 'cultura'], [
            'title' => 'Diagnóstico de Cultura',
            'description' => 'Avaliação da cultura organizacional',
            'plain_id' => 1
        ]);

        $comunicacao = Diagnostic::firstOrCreate(['type' => 'comunicacao'], [
            'title' => 'Diagnóstico de Comunicação Interna',
            'description' => 'Avaliação da comunicação interna',
            'plain_id' => 2
        ]);

        $comunicacaoCampanhas = Diagnostic::firstOrCreate(['type' => 'comunicacao_campanhas'], [
            'title' => 'Diagnóstico de Comunicação Interna com Campanhas',
            'description' => 'Avaliação da comunicação + campanhas automáticas',
            'plain_id' => 3
        ]);

        $culturaQuestions = Question::where('diagnostic_type', 'cultura')->get();
        $comunicacaoQuestions = Question::where('diagnostic_type', 'comunicacao')->get();

        foreach ($culturaQuestions as $question) {
            $cultura->questions()->syncWithoutDetaching([
                $question->id => ['target' => json_encode(['admin', 'user'])]
            ]);
        }

        foreach ($comunicacaoQuestions as $question) {
            $comunicacao->questions()->syncWithoutDetaching([
                $question->id => ['target' => json_encode(['admin', 'user'])]
            ]);

            $comunicacaoCampanhas->questions()->syncWithoutDetaching([
                $question->id => ['target' => json_encode(['admin', 'user'])]
            ]);
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Plain;
use App\Models\Diagnostic;
use App\Models\Question;
use App\Models\Tenant;
use Carbon\Carbon;

class DefaultDiagnosticsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $planos = [
            'básico' => [
                'title' => 'Diagnóstico Básico',
                'description' => 'Versão básica com perguntas essenciais.',
                'categorias' => ['comu_inte', 'reco_valo'],
                'por_categoria' => 2,
            ],
            'intermediário' => [
                'title' => 'Diagnóstico Intermediário',
                'description' => 'Versão intermediária com mais profundidade.',
                'categorias' => ['comu_inte', 'reco_valo', 'clim_orga', 'lide_gest'],
                'por_categoria' => 3,
            ],
            'avançado' => [
                'title' => 'Diagnóstico Avançado',
                'description' => 'Versão completa com todas as categorias.',
                'categorias' => [
                    'comu_inte',
                    'reco_valo',
                    'clim_orga',
                    'cult_orga',
                    'dese_capa',
                    'lide_gest',
                    'qual_vida_trab',
                    'pert_enga',
                ],
                'por_categoria' => 5,
            ]
        ];

        foreach ($planos as $nomePlano => $config) {
            $plain = Plain::where('name', $nomePlano)->first();

            if (!$plain) {
                echo "⚠️ Plano '{$nomePlano}' não encontrado.\n";
                continue;
            }

            $diagnostic = Diagnostic::create([
                'title'       => $config['title'],
                'description' => $config['description'],
                'plain_id'    => $plain->id,
            ]);

            foreach ($config['categorias'] as $categoria) {
                $questions = Question::where('category', $categoria)
                    ->inRandomOrder()
                    ->take($config['por_categoria'])
                    ->get();

                $possiveisTargets = [
                    ['user'],
                    ['admin'],
                    ['user', 'admin'],
                ];

                foreach ($questions as $question) {
                    $targets = $possiveisTargets[array_rand($possiveisTargets)];

                    $diagnostic->questions()->attach($question->id, [
                        'target' => json_encode($targets),
                    ]);
                }
            }

            $tenants = Tenant::where('plain_id', $plain->id)->get();

            foreach ($tenants as $tenant) {
                $diagnostic->tenants()->attach($tenant->id);

                $diagnostic->periods()->create([
                    'tenant_id' => $tenant->id,
                    'start'     => Carbon::now(),
                    'end'       => Carbon::now()->addWeeks(3),
                ]);
            }

            echo "✅ Diagnóstico '{$config['title']}' criado para plano '{$nomePlano}' com " . $diagnostic->questions()->count() . " perguntas.\n";
        }
    }
}

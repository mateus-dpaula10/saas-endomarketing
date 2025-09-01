<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Plain;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Plain::create([
            'name' => 'Diagnóstico de Cultura',
            'type' => 'avulso',
            'price' => 49.90,
            'includes_campaigns' => false,
            'characteristics' => ['Diagnóstico único', 'Relatório PDF'],
        ]);

        Plain::create([
            'name' => 'Diagnóstico de Comunicação Interna',
            'type' => 'avulso',
            'price' => 99.90,
            'includes_campaigns' => false,
            'characteristics' => ['Diagnóstico único', 'Relatório detalhado'],
        ]);

        Plain::create([
            'name' => 'Comunicação Interna + Campanhas Mensais',
            'type' => 'mensal',
            'price' => 199.90,
            'includes_campaigns' => true,
            'characteristics' => ['Diagnóstico inicial', 'Campanhas mensais automáticas', 'Dashboard de resultados'],
        ]);
    }
}

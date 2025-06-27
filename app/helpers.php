<?php

use Illuminate\Support\Str;

if (!function_exists('planoAcao')) {
    function planoAcao($nota)
    {
        return match (true) {
            $nota <= 2 => '⚠️ Ação imediata necessária: há sérias deficiências nos processos, comunicação ou cultura organizacional. Realizar diagnóstico aprofundado e implementar mudanças estruturais com urgência.',
            
            $nota == 3 => '🔧 Ação de melhoria contínua: a organização apresenta desempenho razoável, mas com falhas pontuais. Reforçar boas práticas existentes e corrigir os pontos críticos identificados.',
            
            $nota >= 4 => '✅ Manter e evoluir: os resultados indicam um bom desempenho. Continuar monitorando, promovendo melhorias constantes e buscando inovação para atingir níveis de excelência.',
            
            default => '❓ Plano de ação indisponível: nota inválida ou fora dos parâmetros esperados.',
        };
    }
}
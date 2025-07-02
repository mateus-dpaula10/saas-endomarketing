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

if (!function_exists('planoAcaoCategoria')) {
    function planoAcaoCategoria($categoria, $nota)
    {
        $nomesCategorias = [
            'comu_inte' => 'Comunicação interna',
            'reco_valo' => 'Reconhecimento e Valorização',
            'clim_orga' => 'Clima Organizacional',
            'cult_orga' => 'Cultura Organizacional',
            'dese_capa' => 'Desenvolvimento e Capacitação',
            'lide_gest' => 'Liderança e Gestão',
            'qual_vida_trab' => 'Qualidade de Vida no Trabalho',
            'pert_enga' => 'Pertencimento e Engajamento',
        ];

        $mensagens = [
            'baixo' => [
                'comu_inte' => '⚠️ Comunicação interna falha. É necessário rever canais e processos de comunicação.',
                'reco_valo' => '⚠️ Reconhecimento insuficiente. Implementar programas de valorização do colaborador.',
                'clim_orga' => '⚠️ Clima organizacional crítico. Avaliar conflitos, cultura e ambiente.',
                'cult_orga' => '⚠️ Cultura desalinhada. Iniciar processo de alinhamento cultural.',
                'dese_capa' => '⚠️ Desenvolvimento negligenciado. Criar plano de capacitação estruturado.',
                'lide_gest' => '⚠️ Problemas de liderança. Avaliar e capacitar gestores.',
                'qual_vida_trab' => '⚠️ Baixa qualidade de vida no trabalho. Rever carga e ambiente.',
                'pert_enga' => '⚠️ Falta de pertencimento. Fortalecer engajamento e propósito.'
            ],
            'medio' => [
                'comu_inte' => '🔧 Comunicação interna razoável. Otimizar canais e feedbacks.',
                'reco_valo' => '🔧 Reconhecimento presente, mas inconsistente. Melhorar regularidade.',
                'clim_orga' => '🔧 Clima organizacional mediano. Realizar ações de integração.',
                'cult_orga' => '🔧 Cultura em desenvolvimento. Reforçar valores e missão.',
                'dese_capa' => '🔧 Capacitação moderada. Avaliar aderência às necessidades reais.',
                'lide_gest' => '🔧 Liderança operante. Trabalhar escuta ativa e clareza.',
                'qual_vida_trab' => '🔧 Qualidade de vida ok. Buscar mais equilíbrio e apoio.',
                'pert_enga' => '🔧 Engajamento parcial. Estimular mais participação e propósito.'
            ],
            'alto' => [
                'comu_inte' => '✅ Comunicação bem estabelecida. Manter boas práticas.',
                'reco_valo' => '✅ Reconhecimento efetivo. Continuar e expandir.',
                'clim_orga' => '✅ Clima saudável. Manter escuta ativa e feedbacks.',
                'cult_orga' => '✅ Cultura forte. Reforçar e difundir constantemente.',
                'dese_capa' => '✅ Desenvolvimento consistente. Ampliar trilhas de carreira.',
                'lide_gest' => '✅ Liderança bem avaliada. Estimular mentoria e inovação.',
                'qual_vida_trab' => '✅ Alta qualidade de vida no trabalho. Manter equilíbrio e benefícios.',
                'pert_enga' => '✅ Engajamento forte. Investir em protagonismo e colaboração.'
            ],
        ];

        $faixa = match (true) {
            $nota <= 2 => 'baixo',
            $nota == 3 => 'medio',
            $nota >= 4 => 'alto',
            default => 'default',
        };

        return $mensagens[$faixa][$categoria] ?? '❓ Plano de ação não disponível para esta categoria.';
    }
}
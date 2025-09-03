<?php

use Illuminate\Support\Str;

if (!function_exists('planoAcao')) {
    function planoAcao(float $nota): string
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
    function planoAcaoCategoria(string $categoria, float $nota, string $tipo = 'cultura'): string
    {
        $mensagensCultura = [
            'identidade_proposito' => [
                'baixo' => '⚠️ Identidade e propósito fracos. Reforçar missão, visão e valores.',
                'medio' => '🔧 Identidade e propósito medianos. Promover comunicação interna mais clara.',
                'alto'  => '✅ Identidade e propósito claros. Manter alinhamento cultural.'
            ],
            'valores_comportamentos' => [
                'baixo' => '⚠️ Comportamentos desalinhados. Reforçar valores e padrões.',
                'medio' => '🔧 Comportamentos razoáveis. Ajustar práticas e feedbacks.',
                'alto'  => '✅ Comportamentos alinhados. Continuar incentivando boas práticas.'
            ],
            'ambiente_clima' => [
                'baixo' => '⚠️ Clima organizacional ruim. Avaliar satisfação e engajamento.',
                'medio' => '🔧 Clima razoável. Implementar ações de integração.',
                'alto'  => '✅ Clima saudável. Manter boas práticas de ambiente e cultura.'
            ],
            'comunicacao_lideranca' => [
                'baixo' => '⚠️ Comunicação e liderança deficitárias. Reforçar canais e escuta.',
                'medio' => '🔧 Comunicação razoável. Melhorar feedback e diálogo.',
                'alto'  => '✅ Comunicação eficaz. Manter práticas e liderança engajada.'
            ],
            'processos_praticas' => [
                'baixo' => '⚠️ Processos desalinhados. Revisar procedimentos e tomadas de decisão.',
                'medio' => '🔧 Processos razoáveis. Corrigir pontos críticos.',
                'alto'  => '✅ Processos consistentes. Continuar evoluindo.'
            ],
            'reconhecimento_celebracao' => [
                'baixo' => '⚠️ Reconhecimento insuficiente. Criar programas estruturados.',
                'medio' => '🔧 Reconhecimento razoável. Melhorar consistência.',
                'alto'  => '✅ Reconhecimento efetivo. Manter e expandir.'
            ],
            'diversidade_pertencimento' => [
                'baixo' => '⚠️ Diversidade e pertencimento fracos. Reforçar inclusão.',
                'medio' => '🔧 Diversidade moderada. Ajustar práticas e comunicação.',
                'alto'  => '✅ Diversidade e pertencimento fortes. Continuar promovendo.'
            ],
            'aspiracoes_futuro' => [
                'baixo' => '⚠️ Metas futuras pouco claras. Definir objetivos e comunicação.',
                'medio' => '🔧 Metas razoáveis. Melhorar engajamento e clareza.',
                'alto'  => '✅ Metas bem definidas. Manter acompanhamento e motivação.'
            ],
        ];

        $mensagensComunicacao = [
            'contratar' => [
                'baixo' => '⚠️ Integração e onboarding insuficientes. Revisar processos.',
                'medio' => '🔧 Integração adequada. Melhorar comunicação de informações essenciais.',
                'alto'  => '✅ Integração eficaz. Continuar promovendo boas práticas.'
            ],
            'celebrar' => [
                'baixo' => '⚠️ Reconhecimento de conquistas falho. Implementar celebrações estruturadas.',
                'medio' => '🔧 Reconhecimento razoável. Ajustar frequência e consistência.',
                'alto'  => '✅ Reconhecimento efetivo. Continuar valorizando conquistas.'
            ],
            'compartilhar' => [
                'baixo' => '⚠️ Compartilhamento de informações insuficiente. Melhorar fluxos internos.',
                'medio' => '🔧 Compartilhamento razoável. Ajustar processos e comunicação.',
                'alto'  => '✅ Compartilhamento eficaz. Manter boas práticas.'
            ],
            'inspirar' => [
                'baixo' => '⚠️ Liderança pouco inspiradora. Trabalhar motivação e engajamento.',
                'medio' => '🔧 Liderança razoável. Melhorar comunicação e inspiração.',
                'alto'  => '✅ Liderança inspiradora. Continuar promovendo engajamento.'
            ],
            'falar' => [
                'baixo' => '⚠️ Comunicação aberta limitada. Criar canais seguros.',
                'medio' => '🔧 Comunicação moderada. Incentivar diálogo e feedback.',
                'alto'  => '✅ Comunicação aberta e eficaz. Continuar promovendo transparência.'
            ],
            'escutar' => [
                'baixo' => '⚠️ Liderança pouco receptiva. Melhorar escuta ativa.',
                'medio' => '🔧 Escuta adequada. Ajustar feedback e atenção às sugestões.',
                'alto'  => '✅ Escuta eficaz. Continuar promovendo atenção à equipe.'
            ],
            'cuidar' => [
                'baixo' => '⚠️ Pouco cuidado percebido. Implementar ações de atenção às pessoas.',
                'medio' => '🔧 Cuidado moderado. Reforçar políticas de bem-estar.',
                'alto'  => '✅ Cuidado percebido. Continuar promovendo práticas de atenção e valorização.'
            ],
            'desenvolver' => [
                'baixo' => '⚠️ Desenvolvimento limitado. Criar oportunidades claras de crescimento.',
                'medio' => '🔧 Desenvolvimento adequado. Ajustar programas de capacitação.',
                'alto'  => '✅ Desenvolvimento eficaz. Continuar promovendo evolução profissional.'
            ],
            'agradecer' => [
                'baixo' => '⚠️ Agradecimentos pouco percebidos. Incentivar reconhecimento regular.',
                'medio' => '🔧 Agradecimentos razoáveis. Melhorar frequência e sinceridade.',
                'alto'  => '✅ Agradecimentos claros e valorizados. Continuar promovendo cultura de reconhecimento.'
            ]
        ];

        $faixa = match (true) {
            $nota <= 2 => 'baixo',
            $nota == 3 => 'medio',
            $nota >= 4 => 'alto',
            default    => 'baixo',
        };

        $mensagens = $tipo === 'cultura' ? $mensagensCultura : $mensagensComunicacao;

        return $mensagens[$categoria][$faixa] ?? '❓ Plano de ação não disponível para esta categoria.';
    }
}
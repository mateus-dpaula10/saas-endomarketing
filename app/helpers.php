<?php

use Illuminate\Support\Str;

if (!function_exists('planoAcao')) {
    function planoAcao($nota)
    {
        return match (true) {
            $nota <= 2 => 'Plano de ação urgente: revisar processo, comunicação e cultura.',
            $nota == 3 => 'Plano de ação moderado: reforçar ações atuais e corrigir falhas pontuais.',
            $nota >= 4 => 'Manter e monitorar boas práticas. Buscar excelência.',
            default => 'Sem plano de ação definido.',
        };
    }
}
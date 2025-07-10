<?php

namespace App\Http\Controllers;

use App\Models\Dashboard;
use Illuminate\Http\Request;
use App\Models\Answer;
use App\Models\Question;
use App\Models\Diagnostic;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        $isAdmin = $user->role === 'admin';
        $isSuperAdmin = $user->role === 'superadmin';
        $tenantId = $user->tenant_id;

        $nomesCategorias = [
            'comu_inte' => 'Comunicação interna',
            'reco_valo' => 'Reconhecimento e Valorização',
            'clim_orga' => 'Clima Organizacional',
            'cult_orga' => 'Cultura Organizacional',
            'dese_capa' => 'Desenvolvimento e Capacitação',
            'lide_gest' => 'Liderança e Gestão',
            'qual_vida_trab' => 'Qualidade de Vida no Trabalho',
            'pert_enga' => 'Pertencimento e Engajamento'
        ];

        if ($isSuperAdmin) {
            $respostas = Answer::with(['question', 'period', 'tenant'])
                ->get()
                ->sortBy(fn($resposta) => $resposta->period->start ?? now());

            if ($respostas->isEmpty()) {
                return view('dashboard.index', ['semRespostas' => true]);
            }

            $respostasPorTenant = $respostas->groupBy(fn($r) => $r->tenant->nome ?? 'Empresa desconhecida');
            $analisesPorEmpresa = [];

            foreach ($respostasPorTenant as $nomeEmpresa => $respostasEmpresa) {
                $grupoPorPeriodo = $respostasEmpresa->groupBy('diagnostic_period_id');
                $dadosPorCategoria = [];

                $grupoPorPeriodo->each(function ($grupoPeriodo) use (&$dadosPorCategoria, $nomesCategorias) {
                    $periodo = $grupoPeriodo->first()->period;
                    $periodoLabel = Carbon::parse($periodo->start)->format('d/m/Y') . ' - ' . Carbon::parse($periodo->end)->format('d/m/Y');

                    $grupoPeriodo->groupBy(fn($r) => $r->question->category)
                        ->each(function ($grupoCategoria, $categoria) use (&$dadosPorCategoria, $periodoLabel, $nomesCategorias) {
                            $nomeLegivel = $nomesCategorias[$categoria] ?? ucfirst(str_replace('_', ' ', $categoria));
                            $media = round($grupoCategoria->avg('note'), 2);
                            $dadosPorCategoria[$nomeLegivel][$periodoLabel] = $media;
                        });
                });

                $analisesPorEmpresa[$nomeEmpresa] = $dadosPorCategoria;
            }

            return view('dashboard.index', [
                'analisesPorEmpresa' => $analisesPorEmpresa
            ]);
        }

        if ($isAdmin) {
            $respostas = Answer::with(['question', 'period'])
                ->where('tenant_id', $tenantId)
                ->get()
                ->sortBy(fn($resposta) => $resposta->period->start ?? now());

            if ($respostas->isEmpty()) {
                return view('dashboard.index', [
                    'semRespostas' => true
                ]);
            }

            $grupoPorPeriodo = $respostas->groupBy('diagnostic_period_id');
            $dadosPorCategoria = [];

            $grupoPorPeriodo->each(function ($grupoPeriodo) use (&$dadosPorCategoria, $nomesCategorias) {
                $periodo = $grupoPeriodo->first()->period;
                $periodoLabel = Carbon::parse($periodo->start)->format('d/m/Y') . ' - ' . Carbon::parse($periodo->end)->format('d/m/Y');

                $grupoPeriodo->groupBy(fn($resposta) => $resposta->question->category)
                    ->each(function ($grupoCategoria, $categoria) use (&$dadosPorCategoria, $periodoLabel, $nomesCategorias) {
                        $nomeLegivel = $nomesCategorias[$categoria] ?? ucfirst(str_replace('_', ' ', $categoria));
                        $media = round($grupoCategoria->avg('note'), 2);
                        $dadosPorCategoria[$nomeLegivel][$periodoLabel] = $media;
                    });
            });

            return view('dashboard.index', [
                'evolucaoCategorias' => $dadosPorCategoria
            ]);
        }

        return view('dashboard.index', [
            'mensagem' => 'Bem-vindo! Aqui você verá seus próximos passos ou lembretes da empresa.'
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Dashboard $dashboard)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Dashboard $dashboard)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Dashboard $dashboard)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Dashboard $dashboard)
    {
        //
    }

    public function notification() {
        $user = auth()->user();
        
        $diagnostics = Diagnostic::whereHas('periods', function($query) use ($user) {
            $query->where('tenant_id', $user->tenant_id)
                ->whereDate('start', '<=', now())
                ->whereDate('end', '>=', now());
        })->with(['periods' => function($query) use ($user) {
            $query->where('tenant_id', $user->tenant_id)
                ->whereDate('start', '<=', now())
                ->whereDate('end', '>=', now());
        }])->get();

        $diagnosticsNotAnswered = $diagnostics->filter(function($diagnostic) use ($user) {
            $period = $diagnostic->periods->first();
            return $period && !$diagnostic->answers()
                ->where('user_id', $user->id)
                ->where('diagnostic_period_id', $period->id)
                ->exists();
        })->values();

        $notifications = $diagnosticsNotAnswered->map(function($diag) {
            $period = $diag->periods->first();
            if (!$period) return null;

            return [
                'id' => $diag->id,
                'title' => $diag->title,
                'deadline' => $period->end->toDateString(),
                'days_left' => now()->diffInDays($period->end, false),
            ];
        })->filter()->values();

        return view('dashboard', ['notifications' => $notifications ?? collect()]);
    }
}

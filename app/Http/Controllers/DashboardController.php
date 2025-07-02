<?php

namespace App\Http\Controllers;

use App\Models\Dashboard;
use Illuminate\Http\Request;
use App\Models\Answer;
use App\Models\Question;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        $tenantId = $user->tenant_id;
        $isAdmin = $user->role === 'admin';
        
        if ($isAdmin) {
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
    
            $respostas = Answer::with(['question', 'period'])
                ->where('tenant_id', $tenantId)
                ->get();

            if ($respostas->isEmpty()) {
                return view('dashboard.index', [
                    'semRespostas' => true
                ]);
            }
    
            $grupoPorPeriodo = $respostas->groupBy('diagnostic_period_id');
    
            $respostasPorPeriodo = $grupoPorPeriodo->map(function ($grupoPeriodo) {
                $periodo = $grupoPeriodo->first()->period;
                $porCategoria = $grupoPeriodo->groupBy(fn($resposta) => $resposta->question->category);
    
                $mediasPorCategoria = $porCategoria->map(fn($grupoCategoria) => round($grupoCategoria->avg('note'), 2));
    
                return [
                    'periodo' => $periodo,
                    'categorias' => $mediasPorCategoria
                ];
            });
    
            $dadosPorCategoria = [];
    
            $grupoPorPeriodo->each(function ($grupoPeriodo) use (&$dadosPorCategoria, $nomesCategorias) {
                $periodo = $grupoPeriodo->first()->period;
    
                $periodoLabel = Carbon::parse($periodo->start)->format('d/m/Y') 
                    . ' - ' 
                    . Carbon::parse($periodo->end)->format('d/m/Y');
    
                $grupoPeriodo->groupBy(fn($resposta) => $resposta->question->category)
                    ->each(function ($grupoCategoria, $categoria) use (&$dadosPorCategoria, $periodoLabel, $nomesCategorias) {
                        $nomeLegivel = $nomesCategorias[$categoria] ?? ucfirst(str_replace('_', ' ', $categoria));
                        $media = round($grupoCategoria->avg('note'), 2);
                        $dadosPorCategoria[$nomeLegivel][$periodoLabel] = $media;
                    });
            });
    
            return view('dashboard.index', [
                'dados' => $respostasPorPeriodo,
                'evolucaoCategorias' => $dadosPorCategoria  
            ]);
        } else {
            return view('dashboard.index', [
                'mensagem' => 'Bem-vindo! Aqui você verá seus próximos passos ou lembretes da empresa.'
            ]);
        }
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
}

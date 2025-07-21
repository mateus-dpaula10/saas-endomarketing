<?php

namespace App\Http\Controllers;

use App\Models\Dashboard;
use Illuminate\Http\Request;
use App\Models\Answer;
use App\Models\Question;
use App\Models\Diagnostic;
use App\Models\Campaign;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        $role = $user->role;
        $tenantId = $user->tenant_id;
        $now = Carbon::now();

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

        $campanhas = Campaign::with(['standardCampaign.content'])
            ->where('is_auto', true)
            ->when($role !== 'superadmin', fn($q) => $q->where('tenant_id', $tenantId))
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->orderBy('start_date', 'desc')
            ->get();

        if ($role === 'superadmin') {
            $respostas = Answer::with(['question', 'period', 'tenant'])->get();
            if ($respostas->isEmpty()) {
                return view('dashboard.index', [
                    'semRespostas' => true,
                    'campanhas'    => $campanhas
                ]);
            }

            $respostasPorTenant = $respostas->groupBy(fn($r) => $r->tenant->nome ?? 'Empresa desconhecida');
            $analisesPorEmpresa = [];

            foreach ($respostasPorTenant as $empresa => $respostasEmpresa) {
                $grupoPorPeriodo = $respostasEmpresa->groupBy('diagnostic_period_id');
                $dadosPorCategoria = [];

                $grupoPorPeriodo->each(function ($grupoPeriodo) use (&$dadosPorCategoria, $nomesCategorias) {
                    $periodo = $grupoPeriodo->first()->period;
                    $label = Carbon::parse($periodo->start)->format('d/m/Y') . ' - ' . Carbon::parse($periodo->end)->format('d/m/Y');

                    $grupoPeriodo->groupBy(fn($r) => $r->question->category)
                        ->each(function ($grupoCategoria, $categoria) use (&$dadosPorCategoria, $label, $nomesCategorias) {
                            $nomeLegivel = $nomesCategorias[$categoria] ?? ucfirst(str_replace('_', ' ', $categoria));
                            $media = round($grupoCategoria->avg('note'), 2);
                            $dadosPorCategoria[$nomeLegivel][$label] = $media;
                        });
                });

                $analisesPorEmpresa[$empresa] = $dadosPorCategoria;
            }

            return view('dashboard.index', [
                'analisesPorEmpresa' => $analisesPorEmpresa,
                'campanhas' => $campanhas
            ]);
        }

        $respostas = Answer::with(['question', 'period'])
            ->where('tenant_id', $tenantId)
            ->get();

        $grupoPorPeriodo = $respostas->groupBy('diagnostic_period_id');
        $evolucaoCategorias = [];

        $grupoPorPeriodo->each(function ($grupoPeriodo) use (&$evolucaoCategorias, $nomesCategorias) {
            $periodo = $grupoPeriodo->first()->period;
            if (!$periodo) return;

            $label = Carbon::parse($periodo->start)->format('d/m/Y') . ' - ' . Carbon::parse($periodo->end)->format('d/m/Y');

            $grupoPeriodo->groupBy(fn($resposta) => $resposta->question->category)
                ->each(function ($grupoCategoria, $categoria) use (&$evolucaoCategorias, $label, $nomesCategorias) {
                    $nomeLegivel = $nomesCategorias[$categoria] ?? ucfirst(str_replace('_', ' ', $categoria));
                    $media = round($grupoCategoria->avg('note'), 2);
                    $evolucaoCategorias[$nomeLegivel][$label] = $media;
                });
        });

        $diagnostics = Diagnostic::with(['periods', 'questions'])
            ->whereHas('tenants', fn($q) => $q->where('tenants.id', $tenantId))
            ->get();

        $diagnosticData = collect();

        foreach ($diagnostics as $diagnostic) {
            $period = $diagnostic->periods->where('tenant_id', $tenantId)
                ->filter(fn($p) => $now->between($p->start, $p->end))
                ->first();

            $questions = $diagnostic->questions->filter(fn($q) => $q->pivot && $q->pivot->target === $role);
            $hasQuestions = $questions->isNotEmpty();

            $hasAnswered = false;
            if ($period && $hasQuestions) {
                $hasAnswered = Answer::where('diagnostic_id', $diagnostic->id)
                    ->where('diagnostic_period_id', $period->id)
                    ->where('user_id', $user->id)
                    ->exists();
            }

            $hasAnsweredAnyPeriod = Answer::where('diagnostic_id', $diagnostic->id)
                ->where('user_id', $user->id)
                ->exists();

            $isAvailable = $period && !$hasAnswered && $hasQuestions;

            $diagnosticData->push([
                'diagnostic'            => $diagnostic,
                'period'                => $period,
                'questions'             => $questions,
                'hasQuestions'          => $hasQuestions,
                'hasAnswered'           => $hasAnswered,
                'hasAnsweredAnyPeriod'  => $hasAnsweredAnyPeriod,
                'isAvailable'           => $isAvailable,
            ]);
        }

        return view('dashboard.index', [
            'user'                   => $user,
            'evolucaoCategorias'     => $evolucaoCategorias,
            'availableDiagnostics'   => $diagnosticData->where('isAvailable', true),
            'diagnostics'            => $diagnosticData->where('isAvailable', false),
            'campanhas'              => $campanhas
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

            if (!$period) return false;

            $hasTargetedQuestions = DB::table('diagnostic_question')
                ->join('questions', 'diagnostic_question.question_id', '=', 'questions.id')
                ->where('diagnostic_question.diagnostic_id', $diagnostic->id)
                ->whereJsonContains('diagnostic_question.target', $user->role)
                ->exists();

            if (!$hasTargetedQuestions) return false;

            $alreadyAnswered = $diagnostic->answers()
                ->where('user_id', $user->id)
                ->where('diagnostic_period_id', $period->id)
                ->exists();

            return !$alreadyAnswered;
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

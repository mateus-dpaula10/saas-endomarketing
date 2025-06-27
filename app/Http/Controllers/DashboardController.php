<?php

namespace App\Http\Controllers;

use App\Models\Dashboard;
use Illuminate\Http\Request;
use App\Models\Answer;
use App\Models\Question;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        $tenantId = $user->tenant_id;

        $answers = Answer::where('tenant_id', $tenantId)
            ->with('question')
            ->get()
            ->groupBy(function ($answer) {
                return $answer->diagnostic_period_id;
            });

        $historico = [];

        foreach ($answers as $periodId => $groupedAnswers) {
            $mediaPorCategoria = [];

            foreach ($groupedAnswers->groupBy('question.category') as $categoria => $respostas) {
                $media = $respostas->avg('note');
                $mediaPorCategoria[$categoria] = round($media, 2);
            }

            $historico[$periodId] = $mediaPorCategoria;
        }

        $periodIds = array_keys($historico);
        rsort($periodIds);

        $periodoAtual = $periodIds[0] ?? null;
        $periodoAnterior = $periodIds[1] ?? null;

        $categorias = Question::select('category')->distinct()->pluck('category');

        $comparativo = collect();

        foreach ($categorias as $categoria) {
            $mediaAtual = $historico[$periodoAtual][$categoria] ?? null;
            $mediaAnterior = $historico[$periodoAnterior][$categoria] ?? null;

            $comparativo->push([
                'categoria' => $categoria,
                'atual' => $mediaAtual,
                'anterior' => $mediaAnterior,
                'variacao' => $mediaAnterior !== null && $mediaAtual !== null
                    ? round($mediaAtual - $mediaAnterior, 2)
                    : null
            ]);
        }

        return view('dashboard.index', [
            'comparativo' => $comparativo
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
}

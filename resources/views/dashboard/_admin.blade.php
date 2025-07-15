<button id="exportRelatorioCompleto" class="btn btn-primary my-3 d-block ms-auto">Exportar Relatório Completo em PDF</button>

<div id="relatorioCompleto">
    <h5>Relatório Completo dos Diagnósticos</h5>
    <p><strong>Empresa:</strong> {{ Auth::user()->tenant->nome ?? '---' }}</p>

    <canvas id="graficoEvolucao" width="1000" height="400" style="max-width: 100%; height: auto;"></canvas>

    @foreach ($availableDiagnostics->merge($diagnostics) as $data)
        @php
            $diagnostic = $data['diagnostic'];
            $periods = $diagnostic->periods->where('tenant_id', $user->tenant_id)->sortBy('start');
            $allAnswers = \App\Models\Answer::where('diagnostic_id', $diagnostic->id)
                ->where('tenant_id', $user->tenant_id)->get();
            $mediaGeral = $allAnswers->avg('note');
            $perguntasPorCategoria = $diagnostic->questions->groupBy('category');
            $mediasPorCategoria = [];
            foreach ($perguntasPorCategoria as $categoria => $perguntas) {
                $questionIds = $perguntas->pluck('id')->toArray();
                $respostasCategoria = $allAnswers->whereIn('question_id', $questionIds);
                $mediasPorCategoria[$categoria] = $respostasCategoria->isNotEmpty() ? $respostasCategoria->avg('note') : null;
            }

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
        @endphp

        @if ($allAnswers->isNotEmpty())
            <h5 style="margin-top: 1.5em; margin-bottom: 0.5em;">Diagnóstico '{{ $diagnostic->title }}'</h5>

            @foreach ($periods as $period)
                <h6 style="margin-top: 1em; margin-bottom: 0.3em;">
                    Período: {{ \Carbon\Carbon::parse($period->start)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($period->end)->format('d/m/Y') }}
                </h6>

                @foreach ($diagnostic->questions as $question)
                    @php
                        $answers = \App\Models\Answer::with(['user'])
                            ->where('diagnostic_id', $diagnostic->id)
                            ->where('diagnostic_period_id', $period->id)
                            ->where('question_id', $question->id)
                            ->where('tenant_id', $user->tenant_id)
                            ->get();
                    @endphp

                    @if ($answers->isNotEmpty())
                        @foreach ($answers as $answer)
                            <div style="margin-bottom: 10px; padding-bottom: 6px; border-bottom: 1px solid #ddd;">
                                <p><strong>Pergunta:</strong> {{ $question->text }}</p>
                                <p><strong>Categoria:</strong> {{ $nomesCategorias[$question->category] ?? ucfirst($question->category) }}</p>
                                <p><strong>Função:</strong> {{ $question->pivot->target === 'admin' ? 'Administrador' : 'Colaborador' }}</p>
                                <p><strong>Nota:</strong> {{ $answer->note }}</p>
                            </div>
                        @endforeach
                    @else
                        <div style="margin-bottom: 10px; padding-bottom: 6px; border-bottom: 1px solid #ddd;">
                            <p><strong>Pergunta:</strong> {{ $question->text }}</p>
                            <p><strong>Categoria:</strong> {{ $nomesCategorias[$question->category] ?? ucfirst($question->category) }}</p>
                            <p><strong>Função:</strong> {{ $question->pivot->target === 'admin' ? 'Administrador' : 'Colaborador' }}</p>
                            <p><strong>Status:</strong> Sem resposta</p>
                        </div>
                    @endif
                @endforeach
            @endforeach

            <p style="margin-top: 0.5em; font-weight: bold;">
                Média Geral: {{ number_format($mediaGeral, 2) }} — <small>{{ planoAcao(round($mediaGeral)) }}</small>
            </p>

            <h6>Médias por Categoria:</h6>
            <ul style="padding-left: 1.2em; margin-top: 0.2em; margin-bottom: 1.2em;">
                @foreach ($mediasPorCategoria as $categoria => $media)
                    <li>
                        <strong>{{ $nomesCategorias[$categoria] ?? ucfirst($categoria) }}:</strong>
                        {{ $media ? number_format($media, 2) : 'Sem respostas' }}
                        @if ($media)
                            — {{ planoAcaoCategoria($categoria, round($media)) }}
                        @endif
                    </li>
                @endforeach
            </ul>

            <hr style="margin: 20px 0; border: 1px solid #ccc;">
        @endif
    @endforeach
</div>

<script>
    const dadosCategorias = {!! json_encode($evolucaoCategorias) !!};
    const todosPeriodos = [...new Set(
        Object.values(dadosCategorias).flatMap(categoria => Object.keys(categoria))
    )].sort((a, b) => {
        const [dA, mA, yA] = a.split(' - ')[0].split('/');
        const [dB, mB, yB] = b.split(' - ')[0].split('/');
        return new Date(`${yA}-${mA}-${dA}`) - new Date(`${yB}-${mB}-${dB}`);
    });

    const gerarCor = (index) => {
        const cores = ['#ff6384', '#36a2eb', '#ffcd56', '#4bc0c0', '#9966ff', '#ff9f40', '#8e5ea2', '#3cba9f'];
        return cores[index % cores.length];
    };

    const datasets = Object.entries(dadosCategorias).map(([categoria, periodos], index) => {
        const data = todosPeriodos.map(periodo => periodos[periodo] ?? null);
        return {
            label: categoria,
            data,
            borderColor: gerarCor(index),
            backgroundColor: gerarCor(index),
            fill: false,
            tension: 0.3
        };
    });

    const chartInstance = new Chart(document.getElementById('graficoEvolucao').getContext('2d'), {
        type: 'line',
        data: {
            labels: todosPeriodos,
            datasets
        },
        options: {
            responsive: true,
            plugins: {
                title: { display: true, text: 'Média das Categorias por Período' },
                legend: { position: 'bottom' }
            },
            scales: {
                y: { beginAtZero: true, title: { display: true, text: 'Média' } },
                x: { title: { display: true, text: 'Período' } }
            }
        }
    });

    document.getElementById('exportRelatorioCompleto').addEventListener('click', () => {
        const original = document.getElementById('relatorioCompleto');

        const clone = original.cloneNode(true);

        const canvasOriginal = original.querySelector('#graficoEvolucao');
        const canvasClone = clone.querySelector('#graficoEvolucao');
        if (canvasOriginal && canvasClone) {
            const img = new Image();
            img.src = canvasOriginal.toDataURL('image/png');
            img.style.maxWidth = '100%';
            img.style.height = 'auto';
            canvasClone.replaceWith(img);
        }

        const container = document.createElement('div');
        container.style.position = 'fixed';
        container.style.top = '0';
        container.style.left = '0';
        container.style.width = '0';
        container.style.height = '0';
        container.style.overflow = 'hidden';
        container.style.visibility = 'hidden';
        container.appendChild(clone);
        document.body.appendChild(container);

        html2pdf().set({
            margin: 0.5,
            filename: 'relatorio_completo.pdf',
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: {
                scale: 2,
                useCORS: true
            },
            jsPDF: {
                unit: 'in',
                format: 'a4',
                orientation: 'portrait'
            },
            pagebreak: { mode: ['css', 'legacy'] } 
        }).from(clone).save().then(() => {
            container.remove();
        });
    });
</script>
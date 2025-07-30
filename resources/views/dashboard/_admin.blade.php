<div id="relatorioCompleto">
    <h5>Relatório Completo dos Diagnósticos</h5>
    <p><strong>Empresa:</strong> {{ Auth::user()->tenant->nome ?? '---' }}</p>

    @if(in_array($plano, [2,3]))
        <canvas id="graficoEvolucao" width="1000" height="400" style="max-width: 100%; height: auto;"></canvas>
    @endif

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

                    <div style="margin-bottom: 10px; padding-bottom: 6px; border-bottom: 1px solid #ddd;">
                        <p><strong>Pergunta:</strong> {{ $question->text }}</p>
                        <p><strong>Categoria:</strong> {{ $nomesCategorias[$question->category] ?? ucfirst($question->category) }}</p>
                        <p><strong>Função:</strong> 
                            @php
                                $targets = is_array($question->pivot->target) ? $question->pivot->target : json_decode($question->pivot->target, true);
                            @endphp

                            @if($targets)
                                @foreach($targets as $role)
                                    @if($role === 'user')
                                        Colaborador
                                    @elseif($role === 'admin')
                                        Administrador
                                    @else
                                        Não definido
                                    @endif
                                    @if(!$loop->last)
                                        <span>, </span> 
                                    @endif
                                @endforeach
                            @else
                                Não definido
                            @endif
                        </p>

                        @php
                            $agrupadasPorRole = $answers->groupBy(fn($a) => $a->user->role ?? 'user');
                        @endphp

                        @if ($answers->isNotEmpty())
                            <ul>
                                @foreach ($agrupadasPorRole as $role => $respostas)
                                    <li>
                                        <strong>{{ $role === 'admin' ? 'Administradores' : 'Colaboradores' }}:</strong>
                                        {{ $respostas->count() }} resposta(s),
                                        média {{ number_format($respostas->avg('note'), 2) }}
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p><strong>Status:</strong> Sem resposta</p>
                        @endif
                    </div>
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

    @if (in_array($plano, [2, 3]))
        <button 
            class="btn btn-primary mt-5 d-block ms-auto exportar-relatorio"
            data-plain-id="{{ $plano }}">
            Exportar Relatório Completo em PDF
        </button>
    @endif
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

    document.querySelector('.exportar-relatorio').addEventListener('click', () => {
        const plainId = parseInt(document.querySelector('.exportar-relatorio').getAttribute('data-plain-id'));
        const original = document.getElementById('relatorioCompleto');
        const clone = original.cloneNode(true);

        if (plainId === 2) {
            const grafico = clone.querySelector('#graficoEvolucao');
            if (grafico) grafico.remove();
        }

        if (plainId === 1) {
            alert('Seu plano não permite exportar relatórios.');
            return;
        }

        if (plainId === 3) {
            const canvasOriginal = original.querySelector('#graficoEvolucao');
            const canvasClone = clone.querySelector('#graficoEvolucao');

            if (canvasOriginal && canvasClone) {
                const img = new Image();
                img.src = canvasOriginal.toDataURL('image/png');
                img.style.maxWidth = '100%';
                img.style.height = 'auto';

                img.onload = () => {
                    canvasClone.replaceWith(img);

                    const container = document.createElement('div');
                    container.style.position = 'fixed';
                    container.style.top = '-9999px';
                    container.style.left = '-9999px';
                    container.appendChild(clone);
                    document.body.appendChild(container);

                    html2pdf().set({
                        margin: [0.4, 0.4],
                        filename: `relatorio_diagnostico_completo.pdf`,
                        image: { type: 'jpeg', quality: 0.98 },
                        html2canvas: { scale: 2, useCORS: true },
                        jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' },
                        pagebreak: { mode: ['css', 'legacy'] }
                    }).from(clone).save().then(() => {
                        container.remove();
                    });
                };
                return;
            } 
        }            
        
        html2pdf().set({
            margin: [0.4, 0.4],
            filename: `relatorio_diagnostico_completo.pdf`,
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 2, useCORS: true },
            jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' },
            pagebreak: { mode: ['css', 'legacy'] }
        }).from(clone).save();
    });
</script>
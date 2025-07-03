<h4 class="mb-4">Evolução das Categorias por Empresa</h4>

<ul class="nav nav-tabs" id="empresaTabs" role="tablist">
    @foreach($analisesPorEmpresa as $empresa => $dados)
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $loop->first ? 'active' : '' }}" id="tab-{{ $loop->index }}"
                data-bs-toggle="tab" data-bs-target="#grafico-{{ $loop->index }}"
                type="button" role="tab">
                {{ $empresa }}
            </button>
        </li>
    @endforeach
</ul>

<div class="tab-content mt-3">
    @foreach($analisesPorEmpresa as $empresa => $dados)
        <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="grafico-{{ $loop->index }}" role="tabpanel">
            <canvas id="graficoEmpresa{{ $loop->index }}" width="1000" height="400"></canvas>

            <script>
                (function() {
                    const dados = {!! json_encode($dados) !!};
                    const todosPeriodos = [...new Set(
                        Object.values(dados).flatMap(c => Object.keys(c))
                    )].sort((a, b) => {
                        const [dA, mA, yA] = a.split(' - ')[0].split('/');
                        const [dB, mB, yB] = b.split(' - ')[0].split('/');
                        const dataA = new Date(`${yA}-${mA}-${dA}`);
                        const dataB = new Date(`${yB}-${mB}-${dB}`);
                        return dataA - dataB;
                    });

                    const cores = [
                        '#ff6384', '#36a2eb', '#ffcd56', '#4bc0c0',
                        '#9966ff', '#ff9f40', '#8e5ea2', '#3cba9f'
                    ];

                    const datasets = Object.entries(dados).map(([categoria, periodos], index) => ({
                        label: categoria,
                        data: todosPeriodos.map(p => periodos[p] ?? null),
                        borderColor: cores[index % cores.length],
                        backgroundColor: cores[index % cores.length],
                        fill: false,
                        tension: 0.3
                    }));

                    new Chart(document.getElementById('graficoEmpresa{{ $loop->index }}'), {
                        type: 'line',
                        data: {
                            labels: todosPeriodos,
                            datasets: datasets
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                title: {
                                    display: true,
                                    text: 'Evolução - {{ $empresa }}'
                                },
                                legend: {
                                    position: 'bottom'
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    title: {
                                        display: true,
                                        text: 'Média'
                                    }
                                },
                                x: {
                                    title: {
                                        display: true,
                                        text: 'Período'
                                    }
                                }
                            }
                        }
                    });
                })();
            </script>
        </div>
    @endforeach
</div>
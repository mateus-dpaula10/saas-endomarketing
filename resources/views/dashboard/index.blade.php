@extends('dashboard')

@section('title', 'Dashboard')

@section('content')
    <div class="container dashboard" id="index">
        <div class="row">
            <div class="col-12 py-5">
                @if(isset($semRespostas) && $semRespostas)
                    <div class="alert alert-warning mt-4">
                        Nenhuma resposta registrada ainda para gerar comparação dos diagnósticos.
                    </div>
                @elseif(isset($evolucaoCategorias))
                    <h4 class="mb-4">Evolução das Categorias por Período</h4>
                    <canvas id="graficoEvolucao" width="1000" height="400"></canvas>

                    <script>
                        const dadosCategorias = {!! json_encode($evolucaoCategorias) !!};

                        const todosPeriodos = [...new Set(
                            Object.values(dadosCategorias).flatMap(categoria => Object.keys(categoria))
                        )].sort((a, b) => {
                            const [mA, yA] = a.split('/');
                            const [mB, yB] = b.split('/');
                            return new Date(`01 ${mA} ${yA}`) - new Date(`01 ${mB} ${yB}`);
                        });

                        const gerarCor = (index) => {
                            const cores = [
                                '#ff6384', '#36a2eb', '#ffcd56',
                                '#4bc0c0', '#9966ff', '#ff9f40',
                                '#8e5ea2', '#3cba9f', '#e8c3b9', '#c45850'
                            ];
                            return cores[index % cores.length];
                        };

                        const datasets = Object.entries(dadosCategorias).map(([categoria, periodos], index) => {
                            const data = todosPeriodos.map(periodo => periodos[periodo] ?? null);
                            return {
                                label: categoria,
                                data: data,
                                borderColor: gerarCor(index),
                                backgroundColor: gerarCor(index),
                                fill: false,
                                tension: 0.3
                            };
                        });

                        new Chart(document.getElementById('graficoEvolucao').getContext('2d'), {
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
                                        text: 'Média das Categorias por Período'
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
                    </script>
                @else                    
                    <div class="alert alert-info mt-4">
                        {{ $mensagem ?? 'Bem-vindo!' }}
                        <p class="mb-0">Aqui você poderá acompanhar comunicados, lembretes da empresa ou seus próximos diagnósticos.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    
@endpush
@extends('dashboard')

@section('title', 'Dashboard')

@section('content')
    <div class="container dashboard" id="index">
        <div class="row">
            <div class="col-12 py-5">
                @foreach($comparativo as $category => $data)
                    @php
                        $canvasId = 'grafico_' . str_replace('_', '', $category);
                    @endphp
                    <div class="col-md-4 mb-3">
                        <div class="card mt-4">
                            <div class="card-header">
                                <strong>{{ ucwords(str_replace('_', ' ', $category)) }}</strong>
                            </div>
                            <div class="card-body">
                                <canvas id="{{ $canvasId }}" width="400" height="400"></canvas>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const datasets = @json($comparativo);

            Object.entries(datasets).forEach(([categoria, dados]) => {
                const canvasId = 'grafico_' + categoria.replace(/_/g, '');
                const ctx = document.getElementById(canvasId).getContext('2d');

                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: ['Período Anterior', 'Período Atual'],
                        datasets: [{
                            label: categoria.replace(/_/g, ' '),
                            data: [dados.anterior, dados.atual],
                            backgroundColor: ['rgba(255, 99, 132, 0.7)', 'rgba(54, 162, 235, 0.7)']
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: context => `Média: ${context.raw}`
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 5
                            }
                        }
                    }
                });
            });
        });
    </script>
@endpush
<h4 class="mb-4">Evolução das Categorias por Período</h4>
<canvas id="graficoEvolucao" width="1000" height="400"></canvas>

<script>
    const dadosCategorias = {!! json_encode($evolucaoCategorias) !!};
    const todosPeriodos = [...new Set(
        Object.values(dadosCategorias).flatMap(categoria => Object.keys(categoria))
    )].sort();

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
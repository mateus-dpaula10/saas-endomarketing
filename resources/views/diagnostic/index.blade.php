@extends('dashboard')

@section('title', 'Diagnóstico')

@section('content')
    <div class="container-fluid diagnostico" id="index">
        <div class="row">
            <div class="col-12 py-5">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                    </div>  
                @endif

                <div class="header d-flex justify-content-between align-items-center mb-3">
                    <h4>Diagnósticos</h4>
                </div>
                
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Título</th>
                                <th>Descrição</th>
                                <th>Criado em</th>
                                <th>Plano</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($diagnosticsFiltered as $data)
                                @php
                                    $diagnostic   = $data['diagnostic'];
                                    $hasAnswered  = $data['hasAnswered'];
                                    $hasQuestions = $data['hasQuestions'];
                                @endphp

                                <tr>
                                    <td>{{ $diagnostic->title }}</td>
                                    <td>{{ $diagnostic->description }}</td>
                                    <td>{{ $diagnostic->created_at->format('d/m/Y') }}</td>
                                    <td>{{ $diagnostic->plain->name ?? '-' }}</td>
                                    <td>      
                                        @if (in_array($authUser->role, ['admin', 'user']))  
                                            @if ($authUser->role === 'admin')
                                                <button class="btn btn-primary btn-sm mt-1" data-bs-toggle="modal" data-bs-target="#perguntasModal-{{ $diagnostic->id }}">Visualizar</button>                                                
                                            @endif

                                            @if (!$hasAnswered && $hasQuestions)
                                                <a href="{{ route('diagnostico.answer', $diagnostic) }}" class="btn btn-secondary btn-sm mt-1">Responder</a>
                                            @else
                                                <button class="btn btn-secondary btn-sm mt-1">Já respondeu</button>
                                            @endif                                          
                                        
                                            @if ($authUser->role === 'admin')
                                                <button class="btn btn-alert btn-sm mt-1" data-bs-toggle="modal" data-bs-target="#respostasModal-{{ $diagnostic->id }}">
                                                    Visualizar respostas
                                                </button>
                                                <button class="btn btn-success btn-sm mt-1" data-bs-toggle="modal" data-bs-target="#resultadoModal-{{ $diagnostic->id }}">
                                                    Visualizar resultado do diagnóstico
                                                </button>
                                            @endif                                            
                                        @elseif ($authUser->role === 'superadmin')
                                            <button class="btn btn-primary btn-sm mt-1" data-bs-toggle="modal" data-bs-target="#perguntasModal-{{ $diagnostic->id }}">Visualizar</button>
                                            <a href="{{ route('diagnostico.edit', $diagnostic->id) }}" class="btn btn-warning btn-sm mt-1">Editar</a>
                                            <form action="{{ route('diagnostico.destroy', $diagnostic->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm mt-1">Excluir</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>

                                <div class="modal fade" id="perguntasModal-{{ $diagnostic->id }}" tabindex="-1" aria-labelledby="perguntasModalLabel-{{ $diagnostic->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-xl">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Perguntas - {{ $diagnostic->title }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                                            </div>
                                            <div class="modal-body">
                                                @foreach ($diagnostic->questions as $index => $question)
                                                    @php
                                                        $targetsArray = is_array($question->pivot->target) 
                                                                        ? $question->pivot->target 
                                                                        : json_decode($question->pivot->target ?? '[]', true);
                                                        $targets = implode(', ', $targetsArray);
                                                    @endphp

                                                    <div class="mb-4">                                                
                                                        <label>{{ $index + 1 }} - {{ $question->text }}</label>

                                                        @if ($question->type === 'aberta')
                                                            <textarea class="form-control" rows="1" disabled placeholder="Campo aberto"></textarea>
                                                        @elseif($question->options && $question->options->isNotEmpty())
                                                            @foreach ($question->options as $option)
                                                                <div class="form-check">
                                                                    <input class="form-check-input" type="radio" disabled>
                                                                    <label class="form-check-label">{{ $option->text }}</label>
                                                                </div>
                                                            @endforeach
                                                        @else
                                                            <p class="text-muted">Sem opções disponíveis</p>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                @if ($authUser->role === 'admin')
                                    <div class="modal fade" id="respostasModal-{{ $diagnostic->id }}" tabindex="-1" aria-labelledby="respostasModalLabel-{{ $diagnostic->id }}" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered modal-xl">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Respostas do Diagnóstico '{{ $diagnostic->title }}'</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                                                </div>
                                                <div class="modal-body">
                                                    @foreach ($data['answersGrouped'] as $q)
                                                        <div class="mb-4">
                                                            <label class="py-1 d-flex align-items-center gap-3">
                                                                <strong>{{ $q['question']->text }}</strong>
                                                                <small class="text-muted">
                                                                    <span class="badge bg-secondary">
                                                                        {{ $categoriaFormatada[$q['question']->category] ?? ucfirst(str_replace('_', ' ', $q['question']->category)) }}
                                                                    </span>
                                                                </small>
                                                            </label>

                                                            @if ($q['question']->type === 'fechada')
                                                                <ul>
                                                                    @foreach ($q['answers'] as $note)
                                                                        <li>Nota: {{ $note }}</li>
                                                                    @endforeach
                                                                </ul>
                                                            @else
                                                                @foreach ($q['answers'] as $answer)
                                                                    <div class="p-2 mb-1 border rounded bg-light" style="white-space: pre-wrap">
                                                                        {{ trim($answer['text']) }} 
                                                                    </div>
                                                                @endforeach
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="modal fade" id="resultadoModal-{{ $diagnostic->id }}" tabindex="-1" aria-labelledby="resultadoModalLabel-{{ $diagnostic->id }}" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered modal-xl">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Resultado do Diagnóstico '{{ $diagnostic->title }}'</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                                                </div>
                                                <div class="modal-body">
                                                    @php
                                                        $categoriasCultura = [
                                                            'identidade_proposito'      => 'Identidade e propósito',
                                                            'valores_comportamentos'    => 'Valores e comportamentos',
                                                            'ambiente_clima'            => 'Ambiente e clima',
                                                            'comunicacao_lideranca'     => 'Comunicação e liderança',
                                                            'processos_praticas'        => 'Processos e práticas',
                                                            'reconhecimento_celebracao' => 'Reconhecimento e celebração',
                                                            'diversidade_pertencimento' => 'Diversidade e pertencimento',
                                                            'aspiracoes_futuro'         => 'Aspirações futuras',
                                                        ];
                                                        
                                                        $categoriasComunicacao = [
                                                            'contratar'      => 'Contratação',
                                                            'celebrar'       => 'Celebração',
                                                            'compartilhar'   => 'Compartilhar informações',
                                                            'inspirar'       => 'Inspiração da liderança',
                                                            'falar'          => 'Falar abertamente',
                                                            'escutar'        => 'Escuta da liderança',
                                                            'cuidar'         => 'Cuidado com pessoas',
                                                            'desenvolver'    => 'Desenvolvimento',
                                                            'agradecer'      => 'Agradecimento'
                                                        ];

                                                        $isCultura = ($diagnostic->type ?? 'cultura') == 'cultura'; 

                                                        if ($isCultura) {
                                                            $analiseLabel = 'Cultura Organizacional';
                                                            $analiseAverages = $data['culturaAverages'] ?? collect([]); 
                                                            $analiseAdmin = $data['analisePorRole']['admin'] ?? null;
                                                            $analiseColaborador = $data['analisePorRole']['colaborador'] ?? null;
                                                            $eixosFormatados = $categoriasCultura;
                                                            $chartId = 'culturaRadarChart';
                                                            $dataRoleKey = 'analisePorRole'; 
                                                        } else { 
                                                            $analiseLabel = 'Comunicação';
                                                            $analiseAverages = $data['comunicacaoAverages'] ?? collect([]);
                                                            $analiseAdmin = $data['analiseComunicacaoPorRole']['admin'] ?? null;
                                                            $analiseColaborador = $data['analiseComunicacaoPorRole']['colaborador'] ?? null;
                                                            $eixosFormatados = $categoriasComunicacao;
                                                            $chartId = 'comunicacaoRadarChart';
                                                            $dataRoleKey = 'analiseComunicacaoPorRole'; 
                                                        }

                                                        $hasData = $analiseAverages->filter(fn($v) => $v > 0)->isNotEmpty();
                                                    @endphp

                                                    @if ($hasData)                                                        
                                                        <div class="alert alert-primary text-center">
                                                            <h4>⭐ Pontuação Média Geral da {{ $analiseLabel }}: 
                                                                <span class="badge bg-primary fs-5">{{ $data['overallAverage'] ?? 'N/A' }} / 5</span>
                                                            </h4>
                                                        </div>

                                                        <h4 class="mt-4 mb-3">📝 Resumo Analítico da {{ $analiseLabel }}</h4>

                                                        <div class="row g-4 mb-4">
                                                            <div class="col-md-4">
                                                                <div class="card h-100 border-primary">
                                                                    <div class="card-body">
                                                                        <h6 class="card-title text-primary">Análise da Liderança (Admin)</h6>
                                                                        @if ($analiseAdmin && $analiseAdmin['predominantes']->isNotEmpty())
                                                                            <p class="card-text">
                                                                                **Fortalezas:** {{ $analiseAdmin['predominantes']->keys()->map(fn($k) => $eixosFormatados[$k] ?? $k)->implode(', ') }}
                                                                            </p>
                                                                            <p class="card-text">
                                                                                **Pontos de Atenção:** {{ $analiseAdmin['ausentes']->keys()->map(fn($k) => $eixosFormatados[$k] ?? $k)->implode(', ') }}
                                                                            </p>
                                                                            <small class="text-muted">Ações são mais fortes em **{{ $eixosFormatados[$analiseAdmin['predominantes']->keys()->first()] ?? 'N/A' }}**.</small>
                                                                        @else
                                                                            <p class="text-muted">Sem dados suficientes para análise da Liderança.</p>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            
                                                            <div class="col-md-4">
                                                                <div class="card h-100 border-success">
                                                                    <div class="card-body">
                                                                        <h6 class="card-title text-success">Percepção dos Colaboradores</h6>
                                                                        @if ($analiseColaborador && $analiseColaborador['predominantes']->isNotEmpty())
                                                                            <p class="card-text">
                                                                                **Percepção Positiva:** {{ $analiseColaborador['predominantes']->keys()->map(fn($k) => $eixosFormatados[$k] ?? $k)->implode(', ') }}
                                                                            </p>
                                                                            <p class="card-text">
                                                                                **Maior Crítica:** {{ $analiseColaborador['ausentes']->keys()->map(fn($k) => $eixosFormatados[$k] ?? $k)->implode(', ') }}
                                                                            </p>
                                                                            <small class="text-muted">É percebida como fraca em **{{ $eixosFormatados[$analiseColaborador['ausentes']->keys()->first()] ?? 'N/A' }}**.</small>
                                                                        @else
                                                                            <p class="text-muted">Sem dados suficientes para análise de Colaboradores.</p>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="col-md-4">
                                                                <div class="card h-100 border-warning">
                                                                    <div class="card-body">
                                                                        <h6 class="card-title text-warning">Análise Completa (Média)</h6>
                                                                        @php
                                                                            $analiseOrdenada = $analiseAverages->sortDesc();
                                                                        @endphp
                                                                        <p class="card-text">
                                                                            **Maior Força:** {{ $eixosFormatados[$analiseOrdenada->keys()->first()] ?? 'N/A' }} ({{ $analiseOrdenada->first() }})
                                                                        </p>
                                                                        <p class="card-text">
                                                                            **Pior Desempenho:** {{ $eixosFormatados[$analiseOrdenada->keys()->last()] ?? 'N/A' }} ({{ $analiseOrdenada->last() }})
                                                                        </p>
                                                                        <small class="text-muted">Foco imediato deve ser no tema **{{ $eixosFormatados[$analiseOrdenada->keys()->last()] ?? 'N/A' }}**.</small>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
                                                        <hr>

                                                        <h4 class="mb-3">📊 Comparativo: Líderes (Admin) vs. Colaboradores</h4>

                                                        <div class="d-flex justify-content-center">
                                                            <div style="width: 800px; height: 600px;">
                                                                <canvas id="{{ $chartId }}-{{ $diagnostic->id }}" 
                                                                        data-type="{{ $isCultura ? 'cultura' : 'comunicacao' }}"
                                                                        data-role-key="{{ $dataRoleKey }}"
                                                                        data-averages-key="{{ $isCultura ? 'culturaAverages' : 'comunicacaoAverages' }}">
                                                                </canvas>
                                                            </div>
                                                        </div>

                                                        <hr>

                                                        <h4 class="mb-3">Detalhe das Categorias de {{ $analiseLabel }}</h4>

                                                        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3 mb-4">
                                                            @foreach ($analiseAverages as $categoriaKey => $media)
                                                                @php
                                                                    $bgClass = $media > 4 ? 'bg-success' : ($media > 3 ? 'bg-warning' : 'bg-danger');
                                                                    $categoriaNome = $eixosFormatados[$categoriaKey] ?? $categoriaKey;
                                                                @endphp
                                                                <div class="col">
                                                                    <div class="card text-center h-100 {{ $bgClass }} text-white">
                                                                        <div class="card-body">
                                                                            <h5 class="card-title">{{ $categoriaNome }}</h5>
                                                                            <p class="card-text fs-4">
                                                                                Média: <strong>{{ number_format($media, 2) }} / 5</strong>
                                                                            </p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        <div class="alert alert-warning text-center">
                                                            Não há dados de respostas válidos para calcular a análise deste diagnóstico.
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const categoriasCultura = @json($categoriasCultura ?? []);
        const categoriasComunicacao = @json($categoriasComunicacao ?? []);

        function drawRadarChart(diagnosticId, dataDiagnostics) {
            const canvas = document.querySelector(`#culturaRadarChart-${diagnosticId}, #comunicacaoRadarChart-${diagnosticId}`);
            if (!canvas) return;

            const ctx = canvas.getContext('2d');
            
            if (canvas.chart) {
                canvas.chart.destroy(); 
            }

            const dataType = canvas.getAttribute('data-type');
            const averagesKey = canvas.getAttribute('data-averages-key');
            const roleKey = canvas.getAttribute('data-role-key'); 

            const eixosFormatados = dataType === 'cultura' ? categoriasCultura : categoriasComunicacao;

            const analiseAverages = dataDiagnostics[averagesKey];
            const analisePorRole = dataDiagnostics[roleKey];
            
            if (!analiseAverages || Object.keys(analiseAverages).length === 0) return;

            const rawLabels = Object.keys(analiseAverages);
            const formattedLabels = rawLabels.map(key => eixosFormatados[key] || key);

            const geralValues = Object.values(analiseAverages);
            
            const adminValues = analisePorRole?.admin?.medias ? Object.values(analisePorRole.admin.medias) : new Array(rawLabels.length).fill(0);
            const colaboradorValues = analisePorRole?.colaborador?.medias ? Object.values(analisePorRole.colaborador.medias) : new Array(rawLabels.length).fill(0);

            const data = {
                labels: formattedLabels,
                datasets: [
                    {
                        label: 'Geral (Média da Empresa)',
                        data: geralValues,
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 2,
                    },
                    {
                        label: 'Liderança (Admin)',
                        data: adminValues,
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 2,
                    },
                    {
                        label: 'Colaboradores',
                        data: colaboradorValues,
                        backgroundColor: 'rgba(75, 192, 192, 0.2)', 
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 2,
                    }
                ]
            };

            const config = {
                type: 'radar',
                data: data,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        r: {
                            suggestedMin: 0,
                            suggestedMax: 5,
                            ticks: { stepSize: 1, color: '#666' },
                            pointLabels: {
                                font: { size: 12, weight: 'bold' }
                            }
                        }
                    },
                    plugins: {
                        legend: { position: 'top' },
                    }
                },
            };

            canvas.chart = new Chart(ctx, config);
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('button[data-bs-target^="#resultadoModal-"]').forEach(button => {
                button.addEventListener('click', function() {
                    const diagnosticId = this.getAttribute('data-bs-target').split('-')[1];
                    
                    const dataDiagnostics = @json($diagnosticsFiltered->keyBy('diagnostic.id')[$diagnostic->id] ?? []);

                    if (Object.keys(dataDiagnostics).length > 0) {
                        drawRadarChart(diagnosticId, dataDiagnostics);
                    }
                });
            });
        });
    </script>
    @endpush
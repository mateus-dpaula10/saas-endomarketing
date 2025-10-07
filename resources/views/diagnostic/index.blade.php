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
                                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#perguntasModal-{{ $diagnostic->id }}">Visualizar</button>                                                
                                            @endif

                                            @if (!$hasAnswered && $hasQuestions)
                                                <a href="{{ route('diagnostico.answer', $diagnostic) }}" class="btn btn-secondary btn-sm">Responder</a>
                                            @else
                                                <button class="btn btn-secondary btn-sm">Já respondeu</button>
                                            @endif                                          
                                        
                                            @if ($authUser->role === 'admin')
                                                <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#respostasModal-{{ $diagnostic->id }}">
                                                    Visualizar respostas
                                                </button>
                                            @endif                                            
                                        @elseif ($authUser->role === 'superadmin')
                                            <button class="btn btn-primary btn-sm mb-1" data-bs-toggle="modal" data-bs-target="#perguntasModal-{{ $diagnostic->id }}">Visualizar</button>
                                            <a href="{{ route('diagnostico.edit', $diagnostic->id) }}" class="btn btn-warning btn-sm mb-1">Editar</a>
                                            <form action="{{ route('diagnostico.destroy', $diagnostic->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm mb-1">Excluir</button>
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
                                                                <p>Média das respostas: <strong>{{ number_format($q['average'], 2, ',', '.') }}</strong></p>
                                                            @else
                                                                @foreach ($q['answers'] as $answer)
                                                                    <div class="p-2 mb-1 border rounded bg-light" style="white-space: pre-wrap">
                                                                        {{ trim($answer['text']) }} 
                                                                    </div>
                                                                @endforeach
                                                                <p>Média das respostas: <strong>{{ number_format($q['average_open_sentiment'], 2, ',', '.') }}</strong></p>
                                                            @endif
                                                        </div>
                                                    @endforeach

                                                    @if(!empty($data['categoryAverages']))
                                                        <hr>
                                                        <div class="alert alert-secondary">
                                                            <h5 class="mb-2">Média por categoria:</h5>
                                                            @foreach ($data['categoryAverages'] as $cat => $avg)
                                                                <p>
                                                                    <strong>{{ $categoriaFormatada[$cat] ?? ucfirst(str_replace('_', ' ', $cat)) }}:</strong>
                                                                    {{ number_format($avg, 2, ',', '.') }} 
                                                                    - {{ planoAcaoCategoria($cat, round($avg), 'cultura') }}
                                                                </p>
                                                            @endforeach
                                                        </div>

                                                        <hr>
                                                        <div class="alert alert-info">
                                                            <h5 class="mb-2">Média geral e saúde da empresa:</h5>
                                                            <p>
                                                                <strong>{{ number_format($data['overallAverage'], 2, ',', '.') }}</strong> (média geral)
                                                            </p>
                                                            <p>
                                                                <strong>{{ number_format(($data['overallAverage'] / 5) * 100, 2, ',', '.') }}%</strong> (índice de saúde)
                                                            </p>
                                                            <p>{{ planoAcao(round($data['overallAverage'])) }}</p>
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
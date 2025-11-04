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
                                                                    @foreach ($q['answers'] as $text)
                                                                        <li>{{ $text }}</li>
                                                                    @endforeach
                                                                </ul>
                                                            @else
                                                                @foreach ($q['answers'] as $answer)
                                                                    <textarea class="form-control" rows="5" readonly>{{ trim($answer['text']) }}</textarea>
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
                                                        $roles = ['admin' => 'Liderança / Gestão', 'user' => 'Colaboradores'];
                                                    @endphp

                                                    @foreach ($roles as $roleKey => $roleLabel)
                                                        @php
                                                            $resultado = $data['analisePorRole'][$roleKey] ?? null;
                                                            $resumoRole = $data['resumoPorRole'][$roleKey] ?? null;
                                                        @endphp

                                                        <div class="mb-5">
                                                            <h4 class="mb-3 text-center">📊 {{ $roleLabel }}</h4>                                                            

                                                            @if ($resultado && !empty($resultado['classificacao']))
                                                                <div class="row row-cols-1 row-cols-md-2 g-3">
                                                                    @foreach (['predominante', 'secundario', 'fraco', 'ausente'] as $status)
                                                                        @php
                                                                            $quadrantes = $resultado['classificacao'][$status] ?? [];
                                                                            $badgeClass = match($status) {
                                                                                'predominante' => '#198754',
                                                                                'secundario'   => '#D1E7DD',
                                                                                'fraco'        => '#F8D7DA',
                                                                                'ausente'      => '#DC3545',
                                                                                default        => 'bg-light'
                                                                            };
                                                                            $textColor = match($status) {
                                                                                'secundario'   => '#0a3622',
                                                                                'fraco'        => '#58151c', 
                                                                                default        => '#FFF'
                                                                            };

                                                                            $descricao = match($status) {
                                                                                'predominante' => 'Cultura predominante — representa o quadrante mais forte na organização.',
                                                                                'secundario'   => 'Traços secundários — influenciam, mas não definem o perfil dominante.',
                                                                                'fraco'        => 'Cultura fraca ou ausente — pouco presente nas práticas organizacionais.',
                                                                                'ausente'      => 'Cultura ausente — praticamente inexistente no ambiente atual.',
                                                                                default        => ''
                                                                            }
                                                                        @endphp

                                                                        @foreach ($quadrantes as $quadrante)
                                                                            <div class="col">
                                                                                <div class="card h-100" style="background-color: {{ $badgeClass }}; color: {{ $textColor }}">
                                                                                    <div class="card-body">
                                                                                        <h5 class="card-title">
                                                                                            {{ $quadrante }}
                                                                                        </h5>
                                                                                        <h6 class="card-text mb-2">
                                                                                            {{ $descricao }}
                                                                                        </h6>
                                                                                        {{-- <p class="card-text mb-1">
                                                                                            Média: <strong>{{ $resultado['medias'][$quadrante] ?? 'N/A' }}</strong>
                                                                                        </p> --}}
                                                                                        <small class="card-text mb-1">
                                                                                            Ideal: {{ $culturaContexto[$quadrante]['ideal'] ?? 'N/A' }}
                                                                                        </small> <br>
                                                                                        <small class="card-text mb-1">
                                                                                            Evitar: {{ $culturaContexto[$quadrante]['evitar'] ?? 'N/A' }}
                                                                                        </small>
                                                                                        <p class="card-text mt-2 mb-0">
                                                                                            Sinais: {{ $resultado['sinais'][$quadrante] ?? 'Sem sinais suficientes.' }}
                                                                                        </p>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        @endforeach
                                                                    @endforeach
                                                                </div>
                                                            @else
                                                                <div class="alert alert-warning text-center">
                                                                    Sem dados suficientes para análise de {{ $roleLabel }}.
                                                                </div>
                                                            @endif

                                                            @if ($resumoRole)
                                                                <div class="alert alert-info mt-3">
                                                                    <strong>Resumo geral:</strong> {{ $resumoRole }}
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @endforeach

                                                    @if(!empty($data['resumoGeral']))
                                                        <div class="mb-5">
                                                            <h4 class="mb-3 text-center">📌 Resumo Geral da Organização</h4>
                                                            <div class="alert alert-success">
                                                                {{ $data['resumoGeral'] }}
                                                            </div>
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
        
    </script>
@endpush
@extends('dashboard')

@section('title', 'Diagnóstico')

@section('content')
    <div class="container diagnostico" id="index">
        <div class="row">
            <div class="col-12 py-5">
                @if (session('success'))
                    <p class="alert alert-success">{{ session('success') }}</p>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>  
                @endif

                <div class="header d-flex justify-content-between align-items-center mb-3">
                    <h4>Diagnósticos</h4>
                    @if (auth()->user()->role === 'superadmin')
                        <a href="{{ route('diagnostico.create') }}" class="btn btn-success">
                            <i class="fa-solid fa-plus me-2"></i>Cadastrar diagnóstico
                        </a>
                    @endif
                </div>

                <h5>Disponíveis para resposta</h5>
                @if ($availableDiagnostics->isEmpty())
                    <p class="text-muted bg-light p-3 rounded-1">Nenhum diagnóstico disponível no momento.</p>
                @else
                    <div class="table-responsive mb-5">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Título</th>
                                    <th>Descrição</th>
                                    <th>Período</th>
                                    <th>Criado em</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($availableDiagnostics as $data)
                                    @include('diagnostic._diagnostic_row', ['data' => $data])
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                <h5>Todos os diagnósticos cadastrados</h5>
                @if ($diagnostics->isEmpty())
                    <p class="mb-0 bg-secondary p-3 text-white rounded-1">Nenhum diagnóstico cadastrado.</p>
                @else
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Título</th>
                                    <th>Descrição</th>
                                    <th>Período</th>
                                    <th>Criado em</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($diagnostics as $data)
                                    @include('diagnostic._diagnostic_row', ['data' => $data])
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            @php $user = auth()->user(); @endphp
            @if (in_array($user->role, ['admin', 'user']))
                @foreach ($availableDiagnostics->merge($diagnostics) as $data)
                    @php
                        $diagnostic = $data['diagnostic'];
                        $periods = $diagnostic->periods->where('tenant_id', $user->tenant_id)->sortBy('start');
                        $allQuestions = $diagnostic->questions;

                        $respostas = \App\Models\Answer::with(['question', 'period'])
                            ->where('diagnostic_id', $diagnostic->id)
                            ->where('user_id', $user->id)
                            ->get()
                            ->groupBy('diagnostic_period_id');

                        $mediaGeral = $respostas->flatten()->avg('note');
                        $planoGeral = planoAcao(round($mediaGeral));
                    @endphp

                    @if ($respostas->isNotEmpty())
                        <div class="modal fade" id="respostasModal-{{ $diagnostic->id }}" tabindex="-1" aria-labelledby="respostasModalLabel-{{ $diagnostic->id }}" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-xl">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Respostas do diagnóstico '{{ $diagnostic->title }}'</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                                    </div>
                                    <div class="modal-body">
                                        @foreach ($diagnostic->periods->where('tenant_id', $user->tenant_id)->sortBy('start') as $period)
                                            @php
                                                $start = \Carbon\Carbon::parse($period->start);
                                                $end = \Carbon\Carbon::parse($period->end);

                                                $questions = $diagnostic->questions;

                                                $tenantUserIds = \App\Models\User::where('tenant_id', $user->tenant_id)->pluck('id');

                                                $answers = \App\Models\Answer::with(['question', 'user'])
                                                    ->where('diagnostic_id', $diagnostic->id)
                                                    ->where('diagnostic_period_id', $period->id)
                                                    ->where('tenant_id', $user->tenant_id)
                                                    ->get()
                                                    ->groupBy('question_id');

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

                                            <h6>Período: {{ $start->format('d/m/Y') }} - {{ $end->format('d/m/Y') }}</h6>

                                            <div class="table-responsive">
                                                <table class="table table-bordered table-striped align-middle small">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Pergunta</th>
                                                            <th>Categoria</th>
                                                            <th>Função</th>
                                                            <th>Colaborador</th>
                                                            <th>Nota</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($questions as $question)
                                                            @php
                                                                $respostas = $answers[$question->id] ?? collect();
                                                            @endphp

                                                            @if ($respostas->isNotEmpty())
                                                                @foreach ($respostas as $answer)
                                                                    <tr>
                                                                        <td>{{ $question->text }}</td>
                                                                        <td>{{ $nomesCategorias[$question->category] ?? ucfirst($question->category) }}</td>
                                                                        <td style="text-transform: lowercase">{{ strtoupper($question->pivot->target === 'admin' ? 'Administrador' : 'Colaborador') }}</td>
                                                                        <td>{{ $answer->user->name ?? 'Usuário' }}</td>
                                                                        <td>{{ $answer->note }}</td>
                                                                    </tr>
                                                                @endforeach
                                                            @else
                                                                <tr>
                                                                    <td>{{ $question->text }}</td>
                                                                    <td>{{ $nomesCategorias[$question->category] ?? ucfirst($question->category) }}</td>
                                                                    <td>{{ strtoupper($question->target) }}</td>
                                                                    <td colspan="2">Sem resposta</td>
                                                                </tr>
                                                            @endif
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @endforeach 

                                        @php
                                            $allAnswers = \App\Models\Answer::where('diagnostic_id', $diagnostic->id)
                                                ->where('tenant_id', $user->tenant_id)
                                                ->get();
                                            $mediaGeral = $allAnswers->avg('note');
                                            $perguntasPorCategoria = $diagnostic->questions->groupBy('category');
                                            $mediasPorCategoria = [];
                                            foreach ($perguntasPorCategoria as $categoria => $perguntas) {
                                                $questionIds = $perguntas->pluck('id')->toArray();

                                                $respostasCategoria = $allAnswers->whereIn('question_id', $questionIds);

                                                $mediaCat = $respostasCategoria->isNotEmpty() ? $respostasCategoria->avg('note') : null;

                                                $mediasPorCategoria[$categoria] = $mediaCat;
                                            }
                                        @endphp

                                        <div class="bg-warning p-3 rounded-2 mt-3">
                                            <h5>Média geral: {{ number_format($mediaGeral, 2) }}</h5>
                                            <span style="font-size: 14px">{{ planoAcao(round($mediaGeral)) }}</span>
                                        </div>

                                        <div class="bg-secondary p-3 rounded-2 mt-3 text-white">
                                            <h5 class="my-3">Médias por Categoria</h5>
    
                                            @foreach ($mediasPorCategoria as $categoria => $media)
                                                <div class="mb-2">
                                                    <strong style="font-size: 14px">{{ $nomesCategorias[$categoria] ?? ucfirst($categoria) }}</strong>:
                                                    @if (!is_null($media))
                                                        <strong style="font-size: 14px">{{ number_format($media, 2) }}</strong> —
                                                        <span style="font-size: 14px">{{ planoAcaoCategoria($categoria, round($media)) }}</span>
                                                    @else
                                                        <span>Sem respostas</span>
                                                    @endif
                                                </div>
                                            @endforeach                                        
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            @endif
        </div>
    </div>
@endsection
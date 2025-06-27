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

                <div class="header">
                    <h4>Diagnósticos</h4>
                    @if (auth()->user()->role === 'superadmin')
                        <a href="{{ route('diagnostico.create') }}"><i class="fa-solid fa-plus me-2"></i>Cadastrar diagnóstico</a>
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
                                @foreach ($availableDiagnostics as $diagnostic)
                                    @include('diagnostic._diagnostic_row', ['diagnostic' => $diagnostic])
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
                                @foreach ($diagnostics as $diagnostic)
                                    @include('diagnostic._diagnostic_row', ['diagnostic' => $diagnostic])
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            @if (auth()->user()->role === 'admin')
                @foreach ($diagnostics as $diagnostic)
                    @php
                        $respostasAgrupadas = \App\Models\Answer::with('question')
                            ->where('diagnostic_id', $diagnostic->id)
                            ->where('tenant_id', auth()->user()->tenant_id)
                            ->get()
                            ->groupBy('question_id');

                        $mediaGeral = $respostasAgrupadas
                            ->map(fn ($group) => $group->avg('note'))
                            ->avg();

                        $planoGeral = planoAcao(round($mediaGeral));
                    @endphp

                    <div class="modal fade" id="respostasModal-{{ $diagnostic->id }}" tabindex="-1" aria-labelledby="respostasModalLabel-{{ $diagnostic->id }}" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Respostas do diagnóstico '{{ $diagnostic->title }}'</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                                </div>
                                <div class="modal-body">
                                    @forelse ($respostasAgrupadas as $questionId => $respostas)
                                        @php
                                            $primeiraResposta = $respostas->first();
                                            $mediaNota = $respostas->avg('note');
                                        @endphp

                                        <div class="mb-3">
                                            <strong>{{ $primeiraResposta->question->text }}</strong>
                                            <p class="mb-0">Média das notas: <strong>{{ number_format($mediaNota, 2) }}</strong></p>
                                            <p class="text-muted"><em>{{ planoAcao($mediaNota) }}</em></p>
                                        </div>
                                        <hr>
                                    @empty
                                        <p>Nenhuma resposta encontrada.</p>
                                    @endforelse

                                    <div class="bg-warning p-3 rounded-2">
                                        <strong>Média geral: {{ number_format($mediaGeral, 2) }}</strong><br>
                                        <span>{{ $planoGeral }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
@endsection
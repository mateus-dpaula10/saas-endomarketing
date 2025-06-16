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
                                {{ $error }}
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

                @if ($diagnostics->isEmpty())
                    <p class="mb-0 bg-secondary p-3 text-white rounded-1">Nenhum diagnóstico cadastrado.</p>
                @else
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Título</th>
                                    <th>Descrição</th>
                                    <th>Criado em</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($diagnostics as $diagnostic)
                                    <tr>
                                        <td>{{ $diagnostic->title }}</td>
                                        <td>{{ $diagnostic->description }}</td>
                                        <td>{{ $diagnostic->created_at->format('d/m/Y') }}</td>
                                        <td class="d-flex gap-1 align-items-center">
                                            @php
                                                $user = auth()->user();
                                                $hasAnswered = $diagnostic->answers()->where('tenant_id', $user->tenant_id)->exists();
                                                $hasRelationWithTenant = $diagnostic->tenants->contains('id', $user->tenant_id);
                                            @endphp

                                            @if ($user->role === 'admin' && !$hasAnswered && $hasRelationWithTenant)
                                                <a href="{{ route('diagnostico.answer.form', $diagnostic->id) }}" class="btn btn-primary">Responder</a>
                                            @elseif ($hasAnswered)
                                                <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#respostasModal-{{$diagnostic->id}}">
                                                    Visualizar respostas
                                                </button>
                                            @endif

                                            @if (auth()->user()->role === 'superadmin')
                                                <a href="{{ route('diagnostico.edit', $diagnostic->id) }}" class="btn btn-warning">Editar</a>

                                                <form action="{{ route('diagnostico.destroy', $diagnostic->id) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')

                                                    <button type="submit" class="btn btn-danger">Excluir</button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                <div class="modal fade" id="respostasModal-{{ $diagnostic->id }}" tabindex="-1" aria-labelledby="respostasModalLabel-{{ $diagnostic->id }}" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="respostasModalLabel-{{ $diagnostic->id }}">Respostas do diagnóstico '{{ $diagnostic->title }}'</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                            </div>
                            <div class="modal-body">
                                @php
                                    use Illuminate\Support\Str;

                                    $respostas = \App\Models\Answer::with('question')
                                        ->where('diagnostic_id', $diagnostic->id)
                                        ->where('tenant_id', auth()->user()->tenant_id)
                                        ->get();

                                    $notas = $respostas->pluck('note');
                                    $media = $notas->avg();

                                    function planoAcao($nota) {
                                        return match (true) {
                                            $nota <= 2 => 'Plano de ação urgente: revisar processo, comunicação e cultura.',
                                            $nota == 3 => 'Plano de ação moderado: reforçar ações atuais e corrigir falhas pontuais.',
                                            $nota >= 4 => 'Manter e monitorar boas práticas. Buscar excelência.',
                                            default => 'Sem plano de ação definido.'
                                        };
                                    }

                                    $planoGeral = planoAcao(round($media)); 
                                @endphp

                                @foreach ($respostas as $resposta)
                                    <div class="mb-3">
                                        <strong>{{ $resposta->question->text }}</strong>
                                        <p class="mb-0">Nota: <strong>{{ number_format($resposta->note, 2) }}</strong></p>
                                        <p class="text-muted"><em>{{ planoAcao($resposta->note) }}</em></p>
                                    </div>
                                    <hr>
                                @endforeach

                                <div class="bg-warning p-3">
                                    <strong>Média geral: {{ number_format($media, 2) }}</strong><br>
                                    <span>{{ $planoGeral }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
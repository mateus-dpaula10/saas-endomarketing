@php
    $user = auth()->user();
@endphp

@if (in_array($user->role, ['admin', 'user']))
    @if ($period)
        @if (!$hasAnswered && $hasQuestions)
            <a href="{{ route('diagnostico.answer.form', $diagnostic->id) }}" class="btn btn-primary btn-sm">Responder</a>
        @elseif ($hasAnswered)
            @if ($user->role === 'admin')
                <button class="btn btn-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#respostasModal-{{ $diagnostic->id }}">
                    Visualizar respostas
                </button>
            @else
                <span class="text-muted">Diagnóstico já respondido</span>
            @endif
        @endif
    @else
        @if ($hasAnsweredAnyPeriod)
            @if ($user->role === 'admin')
                <button class="btn btn-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#respostasModal-{{ $diagnostic->id }}">
                    Visualizar respostas
                </button>   
            @else
                <span class="text-muted">Fora do período</span>
            @endif
        @else
            <span class="text-muted">Fora do período</span>
        @endif
    @endif
@elseif ($user->role === 'superadmin')
    <button class="btn btn-primary btn-sm mb-1" data-bs-toggle="modal" data-bs-target="#perguntasModal-{{ $diagnostic->id }}">Visualizar</button>

    <a href="{{ route('diagnostico.edit', ['diagnostico' => $diagnostic->id]) }}" class="btn btn-warning btn-sm mb-1">Editar</a>

    <form action="{{ route('diagnostico.destroy', $diagnostic->id) }}" method="POST" class="d-inline">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger btn-sm mb-1">Excluir</button>
    </form>

    @if ($diagnostic->tenants->isNotEmpty())
        <form action="{{ route('diagnostico.reabrir', ['id' => $diagnostic->id]) }}" method="POST" class="d-inline">
            @csrf
            <select name="tenant" class="form-select form-select-sm d-inline w-auto" required>
                <option value="" disabled selected>Escolha a empresa</option>
                @foreach ($diagnostic->tenants as $tenant)
                    <option value="{{ $tenant->id }}">{{ $tenant->nome }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-success btn-sm mb-1">Liberar período</button>
        </form>
    @endif
@endif

<div class="modal fade" id="perguntasModal-{{ $diagnostic->id }}" tabindex="-1" aria-labelledby="perguntasModalLabel-{{ $diagnostic->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Respostas - {{ $diagnostic->title }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                @php
                    $answers = \App\Models\Answer::where('diagnostic_id', $diagnostic->id)
                        ->where('user_id', $user->id)
                        ->with('question')
                        ->get()
                        ->keyBy('question_id');
                @endphp

                @foreach ($diagnostic->questions as $index => $question)
                    @php
                        $answer = $answers->get($question->id);
                        $value = $answer->value ?? null;
                        $targetsArray = json_decode($question->pivot->target, true);
                        $targets = is_array($targetsArray) ? implode(', ', $targetsArray) : $question->pivot->target;
                    @endphp

                    <div class="mb-4">
                        <label class="form-label fw-bold">
                            {{ $index + 1 }} - {{ $question->text }}
                            <span class="badge bg-info text-dark ms-2" title="Target">{{ $targets }}</span>
                        </label>
                        <div>
                            @for ($i = 1; $i <= 5; $i++)
                                @php
                                    $labels = [
                                        1 => 'Discordo totalmente',
                                        2 => 'Discordo',
                                        3 => 'Neutro',
                                        4 => 'Concordo',
                                        5 => 'Concordo totalmente'
                                    ];
                                @endphp

                                <div class="form-group">
                                    <label class="form-check-label">                                        
                                        <input class="form-check-input" type="radio" 
                                            name="question_{{ $question->id }}" 
                                            value="{{ $i }}" disabled
                                            {{ $value == $i ? 'checked' : '' }}>
                                        {{ $labels[$i] }}
                                    </label>
                                </div>
                            @endfor
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>
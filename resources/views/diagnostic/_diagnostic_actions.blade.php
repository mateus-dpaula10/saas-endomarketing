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
    @if ($diagnostic->tenants->isNotEmpty())
        @php $firstTenantId = $diagnostic->tenants->first()->id; @endphp
        <a href="{{ route('diagnostico.edit', ['diagnostico' => $diagnostic->id, 'tenant' => $firstTenantId]) }}" class="btn btn-warning btn-sm">Editar</a>
    @endif

    <form action="{{ route('diagnostico.destroy', $diagnostic->id) }}" method="POST" class="d-inline">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger btn-sm">Excluir</button>
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
            <button type="submit" class="btn btn-success btn-sm">Liberar período</button>
        </form>
    @endif
@endif
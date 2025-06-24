<tr>
    <td>{{ $diagnostic->title }}</td>
    <td>{{ $diagnostic->description }}</td>
    <td>
        @foreach ($diagnostic->periods as $period)
            <div>
                {{ \Carbon\Carbon::parse($period->start)->format('d/m/Y') }} até
                {{ \Carbon\Carbon::parse($period->end)->format('d/m/Y') }}
            </div>
        @endforeach
    </td>
    <td>{{ $diagnostic->created_at->format('d/m/Y') }}</td>
    @php 
        $user = auth()->user();

        $currentPeriod = $diagnostic->periods
            ->where('tenant_id', $user->tenant_id)
            ->filter(fn($p) => now()->between(Carbon\Carbon::parse($p->start), Carbon\Carbon::parse($p->end)))
            ->first();

        $hasAnswered = $currentPeriod
            ? $diagnostic->answers
                ->where('diagnostic_period_id', $currentPeriod->id)
                ->where('user_id', $user->id)
                ->isNotEmpty()
            : false;

        $hasQuestions = $diagnostic->questions->isNotEmpty();
    @endphp

    @if ($user->role === 'admin')
        <td>        
            @if ($currentPeriod && !$hasAnswered && $hasQuestions)
                <a href="{{ route('diagnostico.answer.form', $diagnostic->id) }}" class="btn btn-primary btn-sm">Responder</a>
            @elseif ($hasAnswered)
                <button class="btn btn-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#respostasModal-{{$diagnostic->id}}">
                    Visualizar respostas
                </button>
            @else
                <span class="text-muted">Fora do período</span>
            @endif
        </td>        
    @elseif ($user->role === 'superadmin')
        <td>
            <a href="{{ route('diagnostico.edit', $diagnostic->id) }}" class="btn btn-warning btn-sm">Editar</a>
            <form action="{{ route('diagnostico.destroy', $diagnostic->id) }}" method="POST" class="d-inline">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm">Excluir</button>
            </form>
            @if ($diagnostic->tenants->isNotEmpty())
                <form action="{{ route('diagnostico.reabrir', ['id' => $diagnostic->id, 'tenant' => $diagnostic->tenants->first()->id]) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success btn-sm">
                        Liberar novo período {{ $diagnostic->tenants->first()->name }}
                    </button>
                </form>
            @endif
        </td>
    @elseif ($user->role === 'user')
        <td>
            @if ($currentPeriod && !$hasAnswered && $hasQuestions)
                <a href="{{ route('diagnostico.answer.form', $diagnostic->id) }}" class="btn btn-primary btn-sm">Responder</a>
            @elseif ($hasAnswered)
                <button class="btn btn-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#respostasModal-{{$diagnostic->id}}">
                    Visualizar respostas
                </button>
            @else
                <span class="text-muted">Fora do período</span>
            @endif
        </td>
    @else
        <td>
            Nenhuma ação
        </td>
    @endif
</tr>
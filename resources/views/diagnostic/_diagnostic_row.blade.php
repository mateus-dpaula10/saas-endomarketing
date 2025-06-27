<tr>
    <td>{{ $diagnostic->title }}</td>
    <td>{{ $diagnostic->description }}</td>
    <td>
        @php
            $user = auth()->user();
        @endphp

        @if ($user->role === 'superadmin')
            @php
                $periodsByTenant = $diagnostic->periods->groupBy('tenant_id');
            @endphp

            @foreach ($periodsByTenant as $tenantId => $periods)
                @php
                    $lastPeriod = $periods->sortByDesc('end')->first();
                @endphp

                @if ($lastPeriod)
                    <div class="mb-2">
                        <strong>{{ $lastPeriod->tenant->nome ?? 'Empresa não definida' }}:</strong><br>
                        {{ \Carbon\Carbon::parse($lastPeriod->start)->format('d/m/Y') }} até
                        {{ \Carbon\Carbon::parse($lastPeriod->end)->format('d/m/Y') }}
                    </div>
                @endif
            @endforeach
        @else
            @php
                $tenantId = $user->tenant_id;
                $periods = $diagnostic->periods->where('tenant_id', $tenantId);
                $lastPeriod = $periods->sortByDesc('end')->first();
            @endphp

            @if ($lastPeriod)
                <div>
                    <strong>{{ $lastPeriod->tenant->nome ?? 'Empresa não definida' }}:</strong><br>
                    {{ \Carbon\Carbon::parse($lastPeriod->start)->format('d/m/Y') }} até
                    {{ \Carbon\Carbon::parse($lastPeriod->end)->format('d/m/Y') }}
                </div>
            @else 
                <div>Sem período para sua empresa</div>
            @endif
        @endif    
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
            @if ($hasAnswered)
                <button class="btn btn-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#respostasModal-{{$diagnostic->id}}">
                    Visualizar respostas
                </button>
            @endif

            @if ($currentPeriod && !$hasAnswered && $hasQuestions)
                <a href="{{ route('diagnostico.answer.form', $diagnostic->id) }}" class="btn btn-primary btn-sm">Responder</a>
            @elseif (!$hasAnswered && !$currentPeriod)
                <span class="text-muted">Fora do período</span>
            @endif
        </td>        
    @elseif ($user->role === 'superadmin')
        <td>
            @if ($diagnostic->tenants->isNotEmpty())
                @php $firstTenantId = $diagnostic->tenants->first()->id; @endphp
                <a href="{{ route('diagnostico.edit', ['diagnostico' => $diagnostic->id, 'tenant' => $firstTenantId]) }}" class="btn btn-warning btn-sm">Editar</a>
            @endif
            <form action="{{ route('diagnostico.destroy', $diagnostic->id) }}" method="POST" class="d-inline">
                @csrf @method('DELETE')
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
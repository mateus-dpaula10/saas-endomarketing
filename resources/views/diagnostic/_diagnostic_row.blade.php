@php
    $user = auth()->user();

    $diagnostic = is_array($data['diagnostic']) && isset($data['diagnostic']['diagnostic']) ? $data['diagnostic']['diagnostic'] : $data['diagnostic'];
    $period = $data['period'];
    $hasQuestions = $data['hasQuestions'];
    $hasAnswered = $data['hasAnswered'];
    $hasAnsweredAnyPeriod = $data['hasAnsweredAnyPeriod'];
@endphp

<tr>
    <td>{{ $diagnostic->title }}</td>
    <td>{{ $diagnostic->description }}</td>
    <td>
        @if ($user->role === 'superadmin')
            @php
                $periodsByTenant = $diagnostic->periods->groupBy('tenant_id');
            @endphp

            @forelse ($periodsByTenant as $tenantId => $periods)
                @php 
                    $active = $periods->first(function ($p) {
                        return now()->between($p->start, $p->end);
                    });                    
                @endphp
                
                @if ($active)
                    <div class="mb-2">
                        <strong>{{ $active->tenant->nome ?? 'Empresa não definida' }}:</strong><br>
                        {{ \Carbon\Carbon::parse($active->start)->format('d/m/Y') }} 
                        até 
                        {{ \Carbon\Carbon::parse($active->end)->format('d/m/Y') }}
                    </div>
                @else
                    <div class="mb-2 text-muted">
                        <strong>{{ $periods->first()->tenant->nome ?? 'Empresa' }}:</strong> Nenhum período ativo no momento
                    </div>
                @endif
            @empty
                <div class="text-muted">Sem períodos cadastrados</div>
            @endforelse
        @else
            @if ($period && now()->between($period->start, $period->end))
                <div>
                    <strong>{{ $period->tenant->nome ?? 'Empresa não definida' }}:</strong><br>
                    {{ \Carbon\Carbon::parse($period->start)->format('d/m/Y') }} 
                    até 
                    {{ \Carbon\Carbon::parse($period->end)->format('d/m/Y') }}
                </div>
            @else 
                <div class="text-muted">Sem período ativo para sua empresa</div>
            @endif
        @endif    
    </td>
    <td>{{ $diagnostic->created_at->format('d/m/Y') }}</td>
    <td>{{ $diagnostic->plain->name }}</td>
    <td>
        @include('diagnostic._diagnostic_actions', [
            'diagnostic' => $diagnostic,
            'period' => $period,
            'hasQuestions' => $hasQuestions,
            'hasAnswered' => $hasAnswered,
            'hasAnsweredAnyPeriod' => $hasAnsweredAnyPeriod
        ])
    </td>
</tr>
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
                @php $last = $periods->sortByDesc('end')->first(); @endphp
                @if ($last)
                    <div class="mb-2">
                        <strong>{{ $last->tenant->nome ?? 'Empresa não definida' }}:</strong><br>
                        {{ \Carbon\Carbon::parse($last->start)->format('d/m/Y') }} 
                        até 
                        {{ \Carbon\Carbon::parse($last->end)->format('d/m/Y') }}
                    </div>
                @endif
            @empty
                <div class="text-muted">Sem períodos cadastrados</div>
            @endforelse
        @else
            @if ($period)
                <div>
                    <strong>{{ $period->tenant->nome ?? 'Empresa não definida' }}:</strong><br>
                    {{ \Carbon\Carbon::parse($period->start)->format('d/m/Y') }} 
                    até 
                    {{ \Carbon\Carbon::parse($period->end)->format('d/m/Y') }}
                </div>
            @else 
                <div class="text-muted">Sem período para sua empresa</div>
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
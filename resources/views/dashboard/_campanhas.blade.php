
<div class="mt-5">
    <h4 class="mb-3">Campanhas em andamento</h4>
    <ul class="list-group">
        @foreach($campanhas as $campanha)
            <li class="list-group-item mt-2" data-bs-toggle="modal" data-bs-target="#campanhaModal{{ $campanha->id }}" style="cursor: pointer">
                <strong>{{ $campanha->text }}</strong><br>
                {{ $campanha->description }}<br>
                <small>
                    Vigência:
                    {{ \Carbon\Carbon::parse($campanha->start_date)->format('d/m/Y') }}
                    até
                    {{ \Carbon\Carbon::parse($campanha->end_date)->format('d/m/Y') }}
                </small>
            </li>
        @endforeach
    </ul>
</div>
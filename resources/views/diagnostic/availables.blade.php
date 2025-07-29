@extends('dashboard')

@section('title', 'Diagnóstico')

@section('content')
    <div class="container-fluid diagnostico" id="index">
        <div class="row">
            <div class="col-12 py-5">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach 
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                    </div>
                @endif

                <div class="header">
                    <h4>Responder diagnóstico '{{ $diagnostic->title }}'</h4>
                    <a href="{{ route('diagnostico.index') }}"><i class="fa-solid fa-arrow-left me-2"></i>Voltar</a>
                </div>
                    
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>{{ $diagnostic->title }}</h5>
                    </div>
                    <div class="card-body">
                        <p>{{ $diagnostic->description }}</p>

                        @php
                            $questions = $diagnostic->questions->filter(function ($q) {
                                $targets = json_decode($q->pivot->target ?? '[]', true);
                                return is_array($targets) && in_array(Auth::user()->role, $targets);
                            });
                        @endphp

                        @if ($questions->isEmpty())
                            <p class="text-muted bg-light p-3 rounded-1">Nenhuma pergunta disponível para seu perfil.</p>
                        @else
                            <form action="{{ route('diagnostico.answer', $diagnostic->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="diagnostic_period_id" value="{{ $currentPeriod->id }}">

                                @foreach ($questions as $index => $question)
                                    <div class="mb-3">
                                        <label class="form-label">{{ $index + 1 }} - {{ $question->text }}</label>                                        
                                        <div>
                                            @php
                                                $labels = [
                                                    1 => 'Discordo totalmente',
                                                    2 => 'Discordo',
                                                    3 => 'Neutro',
                                                    4 => 'Concordo',
                                                    5 => 'Concordo totalmente'
                                                ];
                                            @endphp

                                            @foreach ($labels as $value => $label)
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" 
                                                        name="answers[{{ $question->id }}]" 
                                                        id="question_{{ $question->id }}_{{ $value }}"
                                                        value="{{ $value }}" required>
                                                    <label class="form-check-label" for="question_{{ $question->id }}_{{ $value }}">
                                                        {{ $label }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach

                                <button type="submit" class="btn btn-primary">Salvar</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
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
                                return $q->pivot && $q->pivot->target === Auth::user()->role;
                            });
                        @endphp

                        @if ($diagnostic->questions->isEmpty())
                            <p class="text-muted bg-light p-3 rounded-1">Nenhuma pergunta disponível para seu perfil.</p>
                        @else
                            <form action="{{ route('diagnostico.answer', $diagnostic->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="diagnostic_period_id" value="{{ $currentPeriod->id }}">

                                @foreach ($questions as $question)
                                    <div class="mb-3">
                                        <label class="form-label">{{ $question->text }}</label>
                                        <select name="answers[{{ $question->id }}]" class="form-select" required>
                                            <option value="">Selecione uma opção</option>
                                                @foreach ($question->options as $option)
                                                    <option value="{{ $option->value }}">{{ $option->label }}</option>
                                                @endforeach
                                        </select>
                                    </div>
                                @endforeach

                                <button type="submit" class="btn btn-primary">Enviar respostas</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
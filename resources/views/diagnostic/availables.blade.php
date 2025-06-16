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
                    <h4>Responser diagnóstico '{{ $diagnostic->title }}'</h4>
                    <a href="{{ route('diagnostico.index') }}"><i class="fa-solid fa-arrow-left me-2"></i>Voltar</a>
                </div>
                    
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>{{ $diagnostic->title }}</h5>
                    </div>
                    <div class="card-body">
                        <p>{{ $diagnostic->description }}</p>

                        @php
                            $answer = $diagnostic->answers()->where('tenant_id', auth()->user()->tenant_id)->exists();
                        @endphp

                        @if (!$answer)
                            <form action="{{ route('diagnostico.answer', $diagnostic->id) }}" method="POST">
                                @csrf

                                @foreach ($diagnostic->questions as $question)
                                    <div class="mb-3">
                                        <label class="form-label">{{ $question->text }}</label>
                                        <select name="answers[{{ $question->id }}]" class="form-select" required>
                                            <option value="">Selecione uma nota</option>
                                            @for ($i = 1; $i <= 5; $i++)
                                                <option value="{{ $i }}">{{ $i }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                @endforeach

                                <button type="submit" class="btn btn-primary">Enviar respostas</button>
                            </form>
                        @else
                            <p class="mb-0 bg-secondary p-3 text-white rounded-1">Diagnóstico já respondido pela sua empresa.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
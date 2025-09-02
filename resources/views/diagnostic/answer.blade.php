@extends('dashboard')

@section('title', 'Diagnóstico')

@section('content')
    <div class="container-fluid diagnostico" id="answer">
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

                <div class="header d-flex justify-content-between align-items-center mb-3">
                    <h4>Responder diagnóstico '{{ $diagnostic->title }}'</h4>
                    <a href="{{ route('diagnostico.index') }}"><i class="fa-solid fa-arrow-left me-2"></i>Voltar</a>
                </div>

                <form action="{{ route('diagnostico.submitAnswers', $diagnostic) }}" method="POST">
                    @csrf
                
                    <div class="form-group mt-4">
                        <label class="form-label">Perguntas</label>
                        <div id="questions-wrapper">
                            @foreach ($diagnostic->questions as $index => $question)
                                <div class="question-block mb-3 border rounded p-3 position-relative">
                                    <label class="form-label">Texto da pergunta</label>
                                    <input 
                                        type="text" 
                                        class="form-control" 
                                        value="{{ $question->text }}"
                                        disabled
                                    >

                                    @if ($question->type === 'fechada')                                        
                                        <select 
                                            name="answers[{{ $question->id }}][note]" 
                                            class="form-control mt-2" 
                                            required
                                        >
                                            <option value="">Selecione uma opção</option>                                            
                                            @foreach ($question->options as $option)    
                                                <option value="{{ $option->weight }}">{{ $option->text }}</option>                                                
                                            @endforeach
                                        </select>
                                    @else                                        
                                        <textarea 
                                            name="answers[{{ $question->id }}][text]" 
                                            class="form-control mt-2" 
                                            rows="5" 
                                            placeholder="Escreva sua resposta" 
                                            required
                                        ></textarea>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="form-group mt-4">
                        <button type="submit" class="btn btn-primary">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
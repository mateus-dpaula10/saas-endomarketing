@extends('dashboard')

@section('title', 'Diagnóstico')

@section('content')
    <div class="container diagnostico" id="edit">
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
                    <h4>Editar diagnóstico '{{ $diagnostic->title }}'</h4>
                    <a href="{{ route('diagnostico.index') }}"><i class="fa-solid fa-arrow-left me-2"></i>Voltar</a>
                </div>

                <form action="{{ route('diagnostico.update', $diagnostic->id) }}" method="post">
                    @csrf
                    @method('PATCH')

                    <div class="form-group mt-3">
                        <label for="title" class="form-label">Título</label>
                        <input type="text" class="form-control" name="title" id="title" 
                            value="{{ old('title', $diagnostic->title) }}" required>
                    </div>

                    <div class="form-group mt-3">
                        <label for="description" class="form-label">Descrição</label>
                        <textarea name="description" id="description" rows="5" class="form-control">{{ old('description', $diagnostic->description) }}</textarea>
                    </div>

                    <div class="form-group mt-3">
                        <label for="tenants" class="form-label">Empresas que terão acesso</label>
                        <select name="tenants[]" id="tenants" class="form-select" multiple required>
                            @foreach ($tenants as $tenant)
                                <option value="{{ $tenant->id }}" 
                                    {{ in_array($tenant->id, $diagnostic->tenants->pluck('id')->toArray()) ? 'selected' : '' }}>
                                    {{ $tenant->nome }}
                                </option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Segure Ctrl (Windows) ou Cmd (Mac) para selecionar múltiplas empresas.</small>
                    </div>

                    <div class="form-group mt-4">
                        <label class="form-label">Perguntas</label>
                        <div id="questions-wrapper">
                            @foreach ($diagnostic->questions as $index => $question)
                                <div class="input-group mb-2 question-row">
                                    <input type="hidden" name="question_ids[]" value="{{ $question->id }}">
                                    <input type="text" name="questions[]" class="form-control" value="{{ old('questions.' . $index, $question->text) }}">
                                    <button type="button" class="btn btn-outline-danger" onclick="removeQuestion(this)">x</button>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" class="btn btn-secondary mt-2" onclick="addQuestion()">Adicionar pergunta</button>
                    </div>

                    <div class="form-group mt-4">
                        <button type="submit" class="btn btn-outline-primary">Editar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function addQuestion() {
        const wrapper = document.getElementById('questions-wrapper')

        const div = document.createElement('div')
        div.className = 'input-group mb-2 question-row'

        const input = document.createElement('input')
        input.type = 'text'
        input.name = 'questions[]'
        input.className = 'form-control'

        const hidden = document.createElement('input')
        hidden.type = 'hidden'
        hidden.name = 'question_ids[]'
        hidden.value = '' 

        const button = document.createElement('button')
        button.type = 'button'
        button.className = 'btn btn-outline-danger'
        button.innerText = 'x'
        button.onclick = function() {
            removeQuestion(button)
        }

        div.appendChild(hidden)
        div.appendChild(input)
        div.appendChild(button)

        wrapper.appendChild(div)
    }

    function removeQuestion(button) {
        const row = button.closest('.question-row')
        if (row) {
            row.remove()
        }
    }
    </script>
@endpush
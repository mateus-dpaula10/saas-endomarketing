@extends('dashboard')

@section('title', 'Diagnóstico')

@section('content')
    <div class="container diagnostico" id="create">
        <div class="row">
            <div class="col-12 py-5">
                <div class="header">
                    <h4>Adicionar diagnóstico</h4>
                    <a href="{{ route('diagnostico.index') }}"><i class="fa-solid fa-arrow-left me-2"></i>Voltar</a>
                </div>

                <form action="{{ route('diagnostico.store') }}" method="post">
                    @csrf

                    <div class="form-group mt-3">
                        <label for="title" class="form-label">Título</label>
                        <input type="text" class="form-control" name="title" id="title" required>
                    </div>

                    <div class="form-group mt-3">
                        <label for="description" class="form-label">Descrição</label>
                        <textarea name="description" id="description" rows="5" class="form-control"></textarea>
                    </div>

                    <div class="form-group mt-3">
                        <label for="tenants" class="form-label">Empresas que terão acesso</label>
                        <select name="tenants[]" id="tenants" class="form-select" multiple required>
                            @foreach ($tenants as $tenant)
                                <option value="{{ $tenant->id }}">{{ $tenant->nome }}</option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Segure Ctrl (Windows) ou Cmd (Mac) para selecionar múltiplas empresas.</small>
                    </div>

                    <div class="form-group mt-3" id="questions-wrapper">
                        <label for="" class="form-label">Perguntas</label>
                        <input type="text" name="questions[]" class="form-control">
                    </div>
                    <button type="button" class="btn btn-secondary" onclick="addQuestion()">Adicionar pergunta</button>

                    <button type="submit" class="btn btn-outline-primary mt-3">Cadastrar</button>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function addQuestion() {
            const wrapper = document.getElementById('questions-wrapper')
            const input = document.createElement('input')
            input.type = 'text'
            input.name = 'questions[]'
            input.className = 'form-control mb-2'
            input.required = true
            wrapper.appendChild(input)
        }
    </script>
@endpush
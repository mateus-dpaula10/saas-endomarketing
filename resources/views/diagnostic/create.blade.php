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
                        <label for="start" class="form-label">Início do período</label>
                        <input type="date" class="form-control" name="start" id="start" required>
                    </div>

                    <div class="form-group mt-3">
                        <label for="end" class="form-label">Fim do período</label>
                        <input type="date" class="form-control" name="end" id="end" required>
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

                    <div class="form-group mt-3">
                        <label for="" class="form-label">Perguntas</label>
                        <div id="questions-wrapper">
                            <div class="question-block mb-3 border rounded p-3 position-relative">
                                <label class="form-label">Categoria</label>
                                <select name="questions[0][category]" class="form-select mb-2" required>
                                    <option value="">Selecione uma categoria</option>
                                    <option value="comu_inte">Comunicação Interna</option>
                                    <option value="reco_valo">Reconhecimento e Valorização</option>
                                    <option value="clim_orga">Clima Organizacional</option>
                                    <option value="cult_orga">Cultura Organizacional</option>
                                    <option value="dese_capa">Desenvolvimento e Capacitação</option>
                                    <option value="lide_gest">Liderança e Gestão</option>
                                    <option value="qual_vida_trab">Qualidade de Vida no Trabalho</option>
                                    <option value="pert_enga">Pertencimento e Engajamento</option>
                                </select>

                                <label class="form-label">Texto da pergunta</label>
                                <input type="text" name="questions[0][text]" class="form-control mb-2" placeholder="Digite a pergunta" required>

                                <label class="form-label">Público-alvo</label>
                                <select name="questions[0][target]" class="form-select" required>
                                    <option value="">Selecione o público</option>
                                    <option value="admin">Administrador</option>
                                    <option value="user">Colaborador</option>
                                </select>
                            </div>
                        </div>
                        <button type="button" class="btn btn-secondary mt-2" onclick="addQuestion()">+ Adicionar pergunta</button>
                    </div>

                    <div class="form-group mt-3">
                        <button type="submit" class="btn btn-outline-primary">Cadastrar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let questionIndex = 1

        function addQuestion() {
            const wrapper = document.getElementById('questions-wrapper')

            const block = document.createElement('div')
            block.className = 'question-block mb-3 border rounded p-3 position-relative'
            block.setAttribute('data-index', questionIndex)

            block.innerHTML = `
                <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2" onclick="removeQuestion(this)">
                    Remover
                </button>

                <label class="form-label">Categoria</label>
                <select name="questions[${questionIndex}][category]" class="form-select mb-2" required>
                    <option value="">Selecione uma categoria</option>
                    <option value="comu_inte">Comunicação Interna</option>
                    <option value="reco_valo">Reconhecimento e Valorização</option>
                    <option value="clim_orga">Clima Organizacional</option>
                    <option value="cult_orga">Cultura Organizacional</option>
                    <option value="dese_capa">Desenvolvimento e Capacitação</option>
                    <option value="lide_gest">Liderança e Gestão</option>
                    <option value="qual_vida_trab">Qualidade de Vida no Trabalho</option>
                    <option value="pert_enga">Pertencimento e Engajamento</option>
                </select>

                <label class="form-label">Texto da pergunta</label>
                <input type="text" name="questions[${questionIndex}][text]" class="form-control mb-2" placeholder="Digite a pergunta" required>

                <label class="form-label">Público-alvo</label>
                <select name="questions[${questionIndex}][target]" class="form-select" required>
                    <option value="">Selecione o público</option>
                    <option value="admin">Administrador</option>
                    <option value="user">Colaborador</option>
                </select>
            `
            wrapper.appendChild(block)
            questionIndex++
        }

        function removeQuestion(button) {
            const block = button.closest('.question-block')
            block.remove()
        }
    </script>
@endpush
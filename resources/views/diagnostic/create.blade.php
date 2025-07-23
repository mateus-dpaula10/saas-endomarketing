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
                        <label for="plain_id" class="form-label">Plano</label>
                        <select name="plain_id" id="plain_id" class="form-select" required>
                            <option value="">Selecione um plano</option>
                            @foreach ($plains as $plain)
                                <option value="{{ $plain->id }}">{{ $plain->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mt-3">
                        <label for="" class="form-label">Perguntas</label>
                        <div id="questions-wrapper">
                            <div class="question-block mb-3 border rounded p-3 position-relative" data-index="0">
                                <label class="form-label">Categoria</label>
                                <select name="questions_category[0]" class="form-select mb-2" onchange="atualizarPerguntas(this, 0)" required>
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
                                <select name="questions_text[0]" class="form-select mb-2 question-select" onchange="handleQuestionChange(this, 0)">
                                    <option value="">Digite outra pergunta...</option>
                                </select>

                                <label class="form-label">Público-alvo</label>
                                <select name="questions_target[0][]" class="form-select" multiple required>
                                    <option value="admin">Administrador</option>
                                    <option value="user">Colaborador</option>
                                </select>
                                <small class="form-text text-muted">Segure Ctrl (Windows) ou Cmd (Mac) para selecionar múltiplas empresas.</small>
                            </div>
                        </div>
                        <button type="button" class="btn btn-secondary mt-2" onclick="addQuestion()">+ Adicionar pergunta</button>
                    </div>

                    <div class="form-group mt-3">
                        <button type="submit" class="btn btn-primary">Cadastrar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const perguntasPorCategoria = @json($perguntasPorCategoria);

        let questionIndex = 1;

        function addQuestion() {
            const wrapper = document.getElementById('questions-wrapper');
            const block = document.createElement('div');
            block.className = 'question-block mb-3 border rounded p-3 position-relative';
            block.setAttribute('data-index', questionIndex);

            block.innerHTML = `
                <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2" onclick="removeQuestion(this)">
                    Remover
                </button>

                <label class="form-label">Categoria</label>
                <select name="questions_category[${questionIndex}]" class="form-select mb-2" onchange="atualizarPerguntas(this, ${questionIndex})" required>
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
                <select name="questions_text[${questionIndex}]" class="form-select mb-2 question-select" onchange="handleQuestionChange(this, ${questionIndex})">
                    <option value="">Digite outra pergunta...</option>
                </select>

                <label class="form-label">Público-alvo</label>
                <select name="questions_target[${questionIndex}][]" class="form-select" multiple required>
                    <option value="admin">Administrador</option>
                    <option value="user">Colaborador</option>
                </select>
                <small class="form-text text-muted">Segure Ctrl (Windows) ou Cmd (Mac) para selecionar múltiplas empresas.</small>
            `;
            wrapper.appendChild(block);
            questionIndex++;
        }

        function removeQuestion(button) {
            const block = button.closest('.question-block');
            block.remove();
        }

        function atualizarPerguntas(select, index) {
            const categoria = select.value;
            const perguntas = perguntasPorCategoria[categoria] || [];

            const selectedIds = [];
            document.querySelectorAll('.question-block').forEach(block => {
                const currentIndex = block.getAttribute('data-index');
                if (parseInt(currentIndex) === index) return;

                const catSelect = block.querySelector(`select[name="questions_category[${currentIndex}]"]`);
                const textSelect = block.querySelector(`select[name="questions_text[${currentIndex}]"]`);
                
                if (catSelect?.value === categoria && textSelect?.value) {
                    selectedIds.push(textSelect.value);
                }
            });

            const questionSelect = document.querySelector(`select[name="questions_text[${index}]"]`);
            const questionInput = document.querySelector(`input[name="questions_custom[${index}]"]`);

            questionSelect.innerHTML = `<option value="">Digite outra pergunta...</option>`;

            perguntas.forEach(pergunta => {
                if (!selectedIds.includes(String(pergunta.id))) {
                    const option = document.createElement('option');
                    option.value = pergunta.id;
                    option.textContent = pergunta.text;
                    questionSelect.appendChild(option);
                }
            });

            questionSelect.classList.remove('d-none');
            questionInput.classList.add('d-none');
            questionInput.value = '';
        }

        function handleQuestionChange(select, index) {
            const input = document.querySelector(`input[name="questions_custom[${index}]"]`);
            if (select.value === '') {
                input.classList.remove('d-none');
            } else {
                input.classList.add('d-none');
                input.value = '';
            }
        }
    </script>
@endpush
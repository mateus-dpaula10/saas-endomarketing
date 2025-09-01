@extends('dashboard')

@section('title', 'Diagnóstico')

@section('content')
    <div class="container-fluid diagnostico" id="edit">
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
                        <label for="plain_id" class="form-label">Plano</label>
                        <select name="plain_id" id="plain_id" class="form-select" onchange="carregarPerguntasDoPlano(this.value)">
                            <option value="">Selecione um plano</option>
                            @foreach ($plains as $plain)
                                <option value="{{ $plain->id }}" {{ $diagnostic->plain_id == $plain->id ? 'selected' : '' }}>
                                    {{ $plain->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mt-4">
                        <label class="form-label'">Perguntas</label>
                        <div id="questions-wrapper">
                            @foreach ($diagnostic->questions as $index => $question)
                                <div class="question-block mb-3 border rounded p-3 position-relative">
                                    <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2" onclick="removeQuestion(this)">
                                        Remover
                                    </button>

                                    <input type="hidden" name="question_ids[]" value="{{ $question->id }}">

                                    <label class="form-label">Categoria</label>
                                    <select name="questions_category[]" class="form-select mb-2" required>
                                        @foreach ($categorias as $value => $label)
                                            <option value="{{ $value }}" {{ $question->category === $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <label class="form-label">Texto da pergunta</label>
                                    <select name="questions_text[{{ $index }}]" class="form-select mb-2 question-select" onchange="handleQuestionChange(this, {{ $index }})">
                                        @foreach (($perguntasPorCategoria[$question->category] ?? []) as $pergunta)
                                            <option value="{{ $pergunta['id'] }}" {{ $pergunta['id'] == $question->id ? 'selected' : '' }}>
                                                {{ $pergunta['text'] }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <label class="form-label">Público-alvo</label>
                                    <select name="questions_target[{{ $index }}][]" class="form-select" multiple required>
                                        @php
                                            $targets = is_array($question->target ?? null)
                                                ? $question->target
                                                : json_decode($question->pivot->target ?? '[]', true);
                                        @endphp
                                        <option value="admin" {{ in_array('admin', $targets) ? 'selected' : '' }}>Administrador</option>
                                        <option value="user" {{ in_array('user', $targets) ? 'selected' : '' }}>Colaborador</option>
                                    </select>
                                    <small class="form-text text-muted">Segure Ctrl (Windows) ou Cmd (Mac) para selecionar múltiplas empresas.</small>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" class="btn btn-secondary mt-2" onclick="addQuestion()">Adicionar pergunta</button>
                    </div>

                    <div class="form-group mt-4">
                        <button type="submit" class="btn btn-primary">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let perguntasPorCategoria = @json($perguntasPorCategoria); 
        let categorias = @json($categorias);
        let questionIndex = {{ count($diagnostic->questions ?? []) }};

        function addQuestion() {
            const wrapper = document.getElementById('questions-wrapper');
            const div = document.createElement('div');
            div.className = 'question-block mb-3 border rounded p-3 position-relative';
            div.setAttribute('data-index', questionIndex);

            let categoriaOptions = Object.entries(categorias).map(([value, label]) => {
                return `<option value="${value}">${label}</option>`;
            }).join('');

            div.innerHTML = `
                <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2" onclick="removeQuestion(this)">Remover</button>

                <label class="form-label">Categoria</label>
                <select name="questions_category[${questionIndex}]" class="form-select mb-2" onchange="atualizarPerguntas(this, ${questionIndex})" required>
                    <option value="">Selecione uma categoria</option>
                    ${categoriaOptions}
                </select>

                <label class="form-label">Texto da pergunta</label>
                <select name="questions_text[${questionIndex}]" class="form-select mb-2 question-select" onchange="handleQuestionChange(this, ${questionIndex})">
                    <option value="">Digite outra pergunta...</option>
                </select>

                <input type="text" name="questions_custom[${questionIndex}]" class="form-control mb-2 question-input d-none" placeholder="Digite a pergunta">

                <label class="form-label">Público-alvo</label>
                <select name="questions_target[${questionIndex}][]" class="form-select" multiple required>
                    <option value="admin">Administrador</option>
                    <option value="user">Colaborador</option>
                </select>
                <small class="form-text text-muted">Segure Ctrl (Windows) ou Cmd (Mac) para selecionar múltiplas empresas.</small>
            `;

            wrapper.appendChild(div);
            questionIndex++;
        }

        function removeQuestion(button) {
            const block = button.closest('.question-block');
            if (block) block.remove();
        }

        function atualizarPerguntas(select, index) {
            const categoria = select.value;
            const perguntas = perguntasPorCategoria[categoria] || [];

            const selectedIds = [];
            document.querySelectorAll('.question-block').forEach(block => {
                const textSelect = block.querySelector(`select[name^="questions_text["]`);
                if (textSelect?.value) {
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
                    option.dataset.type = pergunta.type; 
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
                const selectedOption = select.selectedOptions[0];
                if (selectedOption?.dataset.type === 'textarea') {
                    input.classList.remove('d-none');
                } else {
                    input.classList.add('d-none');
                    input.value = '';
                }
            }
        }

        const diagnosticId = {{ $diagnostic->id }};

        function carregarPerguntasDoPlano(plainId) {
            if (!plainId) {
                perguntasPorCategoria = {};
                alert('Selecione um plano válido.');
                return;
            }

            fetch(`/diagnostico/perguntas-por-plano/${plainId}/${diagnosticId}`)
                .then(response => {
                    if (!response.ok) throw new Error('Erro ao buscar perguntas');
                    return response.json();
                })
                .then(data => {
                    perguntasPorCategoria = data;

                    document.querySelectorAll('#questions-wrapper .question-block').forEach(block => {
                        const index = block.getAttribute('data-index');
                        const categorySelect = block.querySelector(`select[name="questions_category[${index}]"]`);
                        const questionSelect = block.querySelector(`select[name="questions_text[${index}]"]`);
                        if (categorySelect && questionSelect) {
                            categorySelect.value = '';
                            questionSelect.innerHTML = `<option value="">Digite outra pergunta...</option>`;
                        }
                    });
                })
                .catch(error => {
                    console.error('Erro no AJAX:', error);
                    alert('Não foi possível carregar perguntas para o plano selecionado.');
                });
        }
    </script>
@endpush
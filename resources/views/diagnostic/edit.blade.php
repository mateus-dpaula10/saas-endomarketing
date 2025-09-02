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
                                <div class="question-block mb-3 border rounded p-3 position-relative" data-index="{{ $index }}">
                                    <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2" onclick="removeQuestion(this)">
                                        Remover
                                    </button>

                                    <input type="hidden" name="question_ids[]" value="{{ $question->id }}">

                                    <label class="form-label">Categoria</label>
                                    <select name="questions_category[{{ $index }}]" class="form-select mb-2" required>
                                        @foreach ($categorias as $value => $label)
                                            <option value="{{ $value }}" {{ $question->category === $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    
                                    <label class="form-label">Tipo</label>
                                    <select name="questions_type[{{ $index }}]" class="form-select mb-2 question-type" onchange="toggleOptions(this, {{ $index }})">
                                        <option value="aberta" {{ $question->type === 'aberta' ? 'selected' : '' }}>Aberta</option>
                                        <option value="fechada" {{ $question->type === 'fechada' ? 'selected' : '' }}>Fechada</option>
                                    </select>

                                    <label class="form-label">Texto da pergunta</label>
                                    <input 
                                        type="text" 
                                        name="questions_text[{{ $index }}]" 
                                        class="form-control mb-2" 
                                        value="{{ old('questions_text.' . $index, $question->text) }}"
                                        required
                                    >

                                    <div class="options-wrapper {{ $question->type === 'fechada' ? '' : 'd-none' }}">
                                        <label class="form-label">Opções</label>
                                        <div class="options-list">
                                            @foreach ($question->options as $optIndex => $option)
                                                <div class="d-flex mb-2 option-item">
                                                    <input type="text" name="questions_options[{{ $index }}][{{ $optIndex }}][text]" class="form-control me-2" placeholder="Opção" value="{{ $option->text }}">
                                                    <input type="number" name="questions_options[{{ $index }}][{{ $optIndex }}][weight]" class="form-control w-25" placeholder="Peso" value="{{ $option->weight }}">
                                                    <button type="button" class="btn btn-danger btn-sm ms-2" onclick="removeOption(this)">x</button>
                                                </div>
                                            @endforeach
                                        </div>
                                        <button type="button" class="btn btn-sm btn-secondary mt-2" onclick="addOption(this, {{ $index }})">Adicionar opção</button>
                                    </div>

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

                <label class="form-label">Tipo</label>
                <select name="questions_type[${questionIndex}]" class="form-select mb-2 question-type" onchange="toggleOptions(this, ${questionIndex})">
                    <option value="aberta">Aberta</option>
                    <option value="fechada">Fechada</option>
                </select>

                <label class="form-label">Texto da pergunta</label>
                <input type="text" name="questions_text[${questionIndex}]" class="form-control mb-2" placeholder="Digite a pergunta" required>

                <div class="options-wrapper d-none">
                    <label class="form-label">Opções</label>
                    <div class="options-list"></div>
                    <button type="button" class="btn btn-sm btn-secondary mt-2" onclick="addOption(this, ${questionIndex})">Adicionar opção</button>
                </div>

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

        function toggleOptions(select, index) {
            const block = document.querySelector(`.question-block[data-index="${index}"]`);
            const optionsWrapper = block.querySelector('.options-wrapper');

            if (select.value === 'fechada') {
                optionsWrapper.classList.remove('d-none');
            } else {
                optionsWrapper.classList.add('d-none');
                optionsWrapper.querySelector('.options-list').innerHTML = '';
            }
        }

        function addOption(button, qIndex) {
            const optionsList = button.closest('.options-wrapper').querySelector('.options-list');
            const optIndex = optionsList.querySelectorAll('.option-item').length;

            const optionDiv = document.createElement('div');
            optionDiv.className = 'd-flex mb-2 option-item';
            optionDiv.innerHTML = `
                <input type="text" name="questions_options[${qIndex}][${optIndex}][text]" class="form-control me-2" placeholder="Opção">
                <input type="number" name="questions_options[${qIndex}][${optIndex}][weight]" class="form-control w-25" placeholder="Peso">
                <button type="button" class="btn btn-danger btn-sm ms-2" onclick="removeOption(this)">x</button>
            `;
            optionsList.appendChild(optionDiv);
        }

        function removeOption(button) {
            button.closest('.option-item').remove();
        }
    </script>
@endpush
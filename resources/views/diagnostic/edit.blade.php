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
                            @foreach ($allTenants as $tenant)
                                <option value="{{ $tenant->id }}" 
                                    {{ in_array($tenant->id, old('tenants', $diagnostic->tenants->pluck('id')->toArray())) ? 'selected' : '' }}>
                                    {{ $tenant->nome }}
                                </option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Segure Ctrl (Windows) ou Cmd (Mac) para selecionar múltiplas empresas.</small>
                    </div>

                    <div id="periods-container" class="mt-4">
                        @foreach ($diagnostic->tenants as $tenant)
                            @php
                                $period = $periodsByTenant[$tenant->id] ?? null;
                            @endphp
                            <div class="period-block border rounded p-3 mb-3" data-tenant-id="{{ $tenant->id }}">
                                <h6>{{ $tenant->nome }}</h6>

                                <input type="hidden" name="tenant_ids[]" value="{{ $tenant->id }}">

                                <div class="mb-2">
                                    <label for="start-{{ $tenant->id }}" class="form-label">Início do período</label>
                                    <input type="date" 
                                        id="start-{{ $tenant->id }}" 
                                        name="start[{{ $tenant->id }}]" 
                                        class="form-control"
                                        value="{{ old('start.' . $tenant->id, optional($period)->start ? \Carbon\Carbon::parse($period->start)->format('Y-m-d') : '') }}"
                                        required>
                                </div>

                                <div>
                                    <label for="end-{{ $tenant->id }}" class="form-label">Fim do período</label>
                                    <input type="date" 
                                        id="end-{{ $tenant->id }}" 
                                        name="end[{{ $tenant->id }}]" 
                                        class="form-control"
                                        value="{{ old('end.' . $tenant->id, optional($period)->end ? \Carbon\Carbon::parse($period->end)->format('Y-m-d') : '') }}"
                                        required>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <div id="period-template" style="display:none;">
                        <div class="period-block border rounded p-3 mb-3" data-tenant-id="">
                            <h6 class="tenant-name"></h6>

                            <input type="hidden" class="tenant-id-input" value="">

                            <div class="mb-2">
                                <label class="form-label">Início do período</label>
                                <input type="date" class="form-control start-input">
                            </div>

                            <div>
                                <label class="form-label">Fim do período</label>
                                <input type="date" class="form-control end-input">
                            </div>
                        </div>
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
                                        <option value="">Selecione uma categoria</option>
                                        @foreach ([
                                            'comu_inte' => 'Comunicação Interna',
                                            'reco_valo' => 'Reconhecimento e Valorização',
                                            'clim_orga' => 'Clima Organizacional',
                                            'cult_orga' => 'Cultura Organizacional',
                                            'dese_capa' => 'Desenvolvimento e Capacitação',
                                            'lide_gest' => 'Liderança e Gestão',
                                            'qual_vida_trab' => 'Qualidade de Vida no Trabalho',
                                            'pert_enga' => 'Pertencimento e Engajamento'
                                            ] as $value => $label)
                                            <option value="{{ $value }}" {{ $question->category === $value ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>

                                    <label class="form-label">Texto da pergunta</label>
                                    <select name="questions_text[{{ $index }}]" class="form-select mb-2 question-select" onchange="handleQuestionChange(this, {{ $index }})">
                                        <option value="">Digite outra pergunta...</option>
                                        @foreach (($perguntasPorCategoria[$question->category] ?? []) as $pergunta)
                                            <option value="{{ $pergunta['id'] }}" {{ $pergunta['id'] == $question->id ? 'selected' : '' }}>
                                                {{ $pergunta['text'] }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <label class="form-label">Público-alvo</label>
                                    <select name="questions_target[{{ $index }}]" class="form-select" required>
                                        <option value="">Selecione o público</option>
                                        <option value="admin" {{ ($question->pivot->target ?? $question->target) === 'admin' ? 'selected' : '' }}>Administrador</option>
                                        <option value="user" {{ ($question->pivot->target ?? $question->target) === 'user' ? 'selected' : '' }}>Colaborador</option>
                                    </select>
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
        const tenantLastPeriods = @json($periodsByTenant);
        const perguntasPorCategoria = @json($perguntasPorCategoria); 

        let questionIndex = {{ count($diagnostic->questions ?? []) }};

        function addQuestion() {
            const wrapper = document.getElementById('questions-wrapper');
            const div = document.createElement('div');
            div.className = 'question-block mb-3 border rounded p-3 position-relative';

            div.innerHTML = `
                <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2" onclick="removeQuestion(this)">Remover</button>

                <label class="form-label">Categoria</label>
                <select name="questions_category[]" class="form-select mb-2" onchange="atualizarPerguntas(this, ${questionIndex})" required>
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
                <select name="questions_target[${questionIndex}]" class="form-select" required>
                    <option value="">Selecione o público</option>
                    <option value="admin">Administrador</option>
                    <option value="user">Colaborador</option>
                </select>
            `;

            wrapper.appendChild(div);
            questionIndex++;
        }

        function removeQuestion(button) {
            const block = button.closest('.question-block');
            if(block) block.remove();
        }

        function atualizarPerguntas(select, index) {
            const categoria = select.value;
            const selectPergunta = document.querySelector(`select[name="questions_text[${index}]"]`);
            const inputCustom = document.querySelector(`input[name="questions_custom[${index}]"]`);

            const perguntas = perguntasPorCategoria[categoria] || [];

            const selectedIds = [];
            document.querySelectorAll('.question-block').forEach((block, idx) => {
                if (idx === index) return;

                const catSelect = block.querySelector(`select[name="questions_category[]"]`);
                const textSelect = block.querySelector(`select[name^="questions_text["]`);
                
                if (catSelect?.value === categoria && textSelect?.value) {
                    selectedIds.push(textSelect.value);
                }
            });

            selectPergunta.innerHTML = `<option value="">Digite outra pergunta...</option>`;
            perguntas.forEach(pergunta => {
                if (!selectedIds.includes(String(pergunta.id))) {
                    const opt = document.createElement('option');
                    opt.value = pergunta.id;
                    opt.textContent = pergunta.text;
                    selectPergunta.appendChild(opt);
                }
            });

            inputCustom?.classList.remove('d-none');
            inputCustom.value = '';
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

        document.addEventListener('DOMContentLoaded', () => {
            const tenantsSelect = document.getElementById('tenants');
            const periodsContainer = document.getElementById('periods-container');
            const periodTemplate = document.getElementById('period-template').firstElementChild;

            function atualizarPeriodos() {
                const selectedTenantIds = Array.from(tenantsSelect.selectedOptions).map(opt => opt.value);

                Array.from(periodsContainer.querySelectorAll('.period-block')).forEach(block => {
                    const tenantId = block.getAttribute('data-tenant-id');
                    if (!selectedTenantIds.includes(tenantId)) {
                        block.remove();
                    }
                });

                selectedTenantIds.forEach(id => {
                    if (!periodsContainer.querySelector(`.period-block[data-tenant-id="${id}"]`)) {
                        const newBlock = periodTemplate.cloneNode(true);
                        newBlock.setAttribute('data-tenant-id', id);

                        const tenantOption = tenantsSelect.querySelector(`option[value="${id}"]`);
                        newBlock.querySelector('.tenant-name').textContent = tenantOption?.textContent || 'Empresa';

                        const tenantIdInput = newBlock.querySelector('.tenant-id-input');
                        tenantIdInput.name = 'tenant_ids[]';
                        tenantIdInput.value = id;

                        const startInput = newBlock.querySelector('.start-input');
                        const endInput = newBlock.querySelector('.end-input');

                        startInput.name = `start[${id}]`;
                        endInput.name = `end[${id}]`;
                        startInput.setAttribute('required', 'required');
                        endInput.setAttribute('required', 'required');

                        if (tenantLastPeriods[id]) {
                            const startVal = tenantLastPeriods[id].start ? tenantLastPeriods[id].start.split(' ')[0] : '';
                            const endVal = tenantLastPeriods[id].end ? tenantLastPeriods[id].end.split(' ')[0] : '';
                            startInput.value = startVal;
                            endInput.value = endVal;
                        }

                        periodsContainer.appendChild(newBlock);
                    }
                });
            }

            atualizarPeriodos(); 
            tenantsSelect.addEventListener('change', atualizarPeriodos);
        });
    </script>
@endpush
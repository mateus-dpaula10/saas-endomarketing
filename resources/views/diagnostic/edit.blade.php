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
                        <select name="plain_id" id="plain_id" class="form-select">
                            <option value="">Selecione um plano</option>
                            @foreach ($plains as $plain)
                                <option value="{{ $plain->id }}" {{ $diagnostic->plain_id == $plain->id ? 'selected' : '' }}>
                                    {{ $plain->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mt-3">
                        <label for="tenants" class="form-label">Empresas associadas</label>
                        <select name="tenants[]" id="tenants" class="form-select" multiple>
                            @foreach ($allTenants as $tenant)
                                <option value="{{ $tenant->id }}" 
                                    {{ in_array($tenant->id, old('tenants', $linkedTenants->pluck('id')->toArray())) ? 'selected' : '' }}>
                                    {{ $tenant->nome }}
                                </option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Segure Ctrl (Windows) ou Cmd (Mac) para selecionar múltiplas empresas.</small>
                    </div>

                    <div id="periods-container-fluid" class="mt-4">
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
                        <button type="submit" class="btn btn-primary">Editar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const tenantLastPeriods = @json($periodsByTenant);
        const savedPeriods = { ...tenantLastPeriods };
        const perguntasPorCategoria = @json($perguntasPorCategoria); 

        let questionIndex = {{ count($diagnostic->questions ?? []) }};

        function addQuestion() {
            const wrapper = document.getElementById('questions-wrapper');
            const div = document.createElement('div');
            div.className = 'question-block mb-3 border rounded p-3 position-relative';
            div.setAttribute('data-index', questionIndex);

            div.innerHTML = `
                <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2" onclick="removeQuestion(this)">Remover</button>

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

        document.addEventListener('DOMContentLoaded', () => {
            const plainSelect = document.getElementById('plain_id');
            const tenantsSelect = document.getElementById('tenants');
            const periodscontainer-fluid = document.getElementById('periods-container-fluid');
            const periodTemplate = document.getElementById('period-template').firstElementChild;

            function atualizarPeriodos() {
                const selectedTenantIds = Array.from(tenantsSelect.selectedOptions).map(opt => opt.value);

                Array.from(periodscontainer-fluid.querySelectorAll('.period-block')).forEach(block => {
                    const tenantId = block.getAttribute('data-tenant-id');
                    if (!selectedTenantIds.includes(tenantId)) {
                        const startVal = block.querySelector('input[name^="start"]').value;
                        const endVal = block.querySelector('input[name^="end"]').value;

                        savedPeriods[tenantId] = {
                            start: startVal,
                            end: endVal
                        };

                        block.remove();
                    }
                });

                selectedTenantIds.forEach(id => {
                    if (!periodscontainer-fluid.querySelector(`.period-block[data-tenant-id="${id}"]`)) {
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

                        if (savedPeriods[id]) {
                            startInput.value = savedPeriods[id].start || '';
                            endInput.value = savedPeriods[id].end || '';
                        }

                        periodscontainer-fluid.appendChild(newBlock);
                    }
                });
            }

            tenantsSelect.addEventListener('change', atualizarPeriodos);
            atualizarPeriodos(); 

            plainSelect.addEventListener('change', function () {
                const plainId = this.value;
                
                if (!plainId) {
                    tenantsSelect.innerHTML = '';
                    tenantsSelect.dispatchEvent(new Event('change'));
                    return;
                }

                fetch(`/diagnostico/empresas-por-plano/${plainId}`)
                    .then(response => response.json())
                    .then(data => {
                        tenantsSelect.innerHTML = '';
                        data.forEach(tenant => {
                            const option = document.createElement('option');
                            option.value = tenant.id;
                            option.text = tenant.nome;
                            tenantsSelect.appendChild(option);
                        });

                        fetch(`/diagnostico/periodos-por-plano/${plainId}`)
                            .then(resp => resp.json())
                            .then(periodData => {                                
                                Object.assign(tenantLastPeriods, periodData);
                                tenantsSelect.dispatchEvent(new Event('change'));
                            });
                    });
            });
        });
    </script>
@endpush
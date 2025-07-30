@extends('dashboard')

@section('title', 'Empresa')

@section('content')
    <div class="container-fluid empresa" id="edit">
        <div class="row">
            <div class="col-12 py-5">
                <div class="header">
                    <h4>Editar empresa '{{ $empresa->nome }}'</h4>
                    <a href="{{ route('empresa.index') }}"><i class="fa-solid fa-arrow-left me-2"></i>Voltar</a>
                </div>

                <form action="{{ route('empresa.update', $empresa->id) }}" method="POST">
                    @csrf
                    @method('PATCH')

                    <div class="form-group mt-3">
                        <label for="nome" class="form-label">Nome</label>
                        <input type="text" class="form-control" name="nome" id="nome" value="{{ old('nome', $empresa->nome) }}" required>
                    </div>

                    <div class="form-group mt-3">
                        <label for="plain_id" class="form-label">Plano</label>
                        <select name="plain_id" id="plain_id" class="form-select" required>
                            @foreach ($plains as $plain)
                                <option value="{{ $plain->id }}" {{ old('plain_id', $empresa->plain_id) == $plain->id ? 'selected' : '' }}>{{ $plain->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mt-3">
                        <label for="active_tenant" class="form-label">Ativo</label>
                        <select name="active_tenant" id="active_tenant" class="form-select" required>
                            <option value="0" {{ old('active_tenant', $empresa->active_tenant) == 0 ? 'selected' : '' }}>Não</option>
                            <option value="1" {{ old('active_tenant', $empresa->active_tenant) == 1 ? 'selected' : '' }}>Sim</option>
                        </select>
                    </div>

                    <div class="form-group mt-3">
                        <label for="cnpj" class="form-label">CNPJ</label>
                        <input type="text" class="form-control" name="cnpj" id="cnpj" value="{{ old('cnpj', $empresa->cnpj) }}" required>
                    </div>

                    <div id="cnpj-loader" class="mt-2 text-primary" style="display: none;">
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        Buscando dados do CNPJ...
                    </div>

                    <div class="form-group mt-3">
                        <label for="social_reason" class="form-label">Razão social</label>
                        <input type="text" class="form-control" name="social_reason" id="social_reason" value="{{ old('social_reason', $empresa->social_reason) }}">
                    </div>

                    <div class="form-group mt-3">
                        <label for="fantasy_name" class="form-label">Nome fantasia</label>
                        <input type="text" class="form-control" name="fantasy_name" id="fantasy_name" value="{{ old('fantasy_name', $empresa->fantasy_name) }}">
                    </div>

                    <div class="form-group mt-3">
                        <label for="address" class="form-label">Endereço</label>
                        <input type="text" class="form-control" name="address" id="address" value="{{ old('address', $empresa->address) }}">
                    </div>

                    <div class="form-group mt-3">
                        <label for="bairro" class="form-label">Bairro</label>
                        <input type="text" class="form-control" name="bairro" id="bairro" value="{{ old('bairro', $empresa->bairro) }}">
                    </div>

                    <div class="form-group mt-3">
                        <label for="cep" class="form-label">CEP</label>
                        <input type="text" class="form-control" name="cep" id="cep" value="{{ old('cep', $empresa->cep) }}">
                    </div>

                    <div class="form-group mt-3">
                        <label for="telephone" class="form-label">Telefone</label>
                        <input type="text" class="form-control" name="telephone" id="telephone" value="{{ old('telephone', $empresa->telephone) }}">
                    </div>

                    <div class="form-group mt-3">
                        <label for="contract_start" class="form-label">Início do contrato</label>
                        <input type="date" class="form-control" name="contract_start" id="contract_start" value="{{ old('contract_start', isset($empresa) ? $empresa->contract_start->format('Y-m-d') : date('Y-m-d')) }}" required>
                    </div>

                    <button type="submit" class="btn btn-outline-primary mt-3">Editar</button>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function maskTelephone(input) {
            let v = input.value.replace(/\D/g, '');

            if (v.length <= 10) {
                input.value = v.replace(/^(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
            } else {
                input.value = v.replace(/^(\d{2})(\d{5})(\d{0,4})/, '($1) $2-$3');
            }
        };

        function maskCep(input) {
            let v = input.value.replace(/\D/g, '');
            input.value = v.replace(/^(\d{5})(\d{0,3})/, '$1-$2');
        }

        document.getElementById('telephone').addEventListener('input', function() {
            maskTelephone(this);
        });

        document.getElementById('cep').addEventListener('input', function() {
            maskCep(this);
        });

        document.getElementById('cnpj').addEventListener('blur', function() {
            let cnpj = this.value.replace(/\D/g, '');

            if (cnpj.length !== 14) {
                alert('CNPJ inválido.');
                return;
            }

            const loader = document.getElementById('cnpj-loader');
            loader.style.display = 'block';

            fetch(`/consulta-cnpj/${cnpj}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('social_reason').value = data.nome || 'Sem razão social cadastrada';
                    document.getElementById('fantasy_name').value = data.fantasia || 'Sem nome fantasia cadastrado';
                    document.getElementById('address').value = `${data.logradouro || ''}, ${data.numero || ''}` || 'Sem endereço cadastrado';
                    document.getElementById('bairro').value = data.bairro || 'Sem bairro cadastrado';
                    document.getElementById('cep').value = data.cep || 'Sem cep cadastrado';
                    document.getElementById('telephone').value = data.telefone || 'Sem telefone cadastrado';

                    maskCep(document.getElementById('cep'));
                    maskTelephone(document.getElementById('telephone'));
                })
                .catch(error => {
                    console.error('Erro ao consultar CNPJ:', error);
                    alert('Erro ao consultar CNPJ.');
                })
                .finally(() => {
                    loader.style.display = 'none';
                });
        });
    </script>
@endpush
@extends('dashboard')

@section('title', 'Empresa')

@section('content')
    <div class="container empresa" id="create">
        <div class="row">
            <div class="col-12 py-5">
                <div class="header">
                    <h4>Adicionar empresa</h4>
                    <a href="{{ route('empresa.index') }}"><i class="fa-solid fa-arrow-left me-2"></i>Voltar</a>
                </div>
                
                <form action="{{ route('empresa.store') }}" method="POST">
                    @csrf

                    <div class="form-group mt-3">
                        <label for="nome" class="form-label">Nome</label>
                        <input type="text" class="form-control" name="nome" id="nome" required>
                    </div>

                    <div class="form-group mt-3">
                        <label for="plain" class="form-label">Plano</label>
                        <select name="plain" id="plain" class="form-select" required>
                            <option value="" selected>Selecione um plano</option>
                            <option value="basic">Básico</option>
                            <option value="intermediary">Intermediário</option>
                            <option value="advanced">Avançado</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-outline-primary mt-3">Cadastrar</button>
                </form>
            </div>
        </div>
    </div>
@endsection
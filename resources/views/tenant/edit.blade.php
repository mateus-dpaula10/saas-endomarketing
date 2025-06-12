@extends('dashboard')

@section('title', 'Empresa')

@section('content')
    <div class="container empresa" id="edit">
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
                        <label for="dominio" class="form-label">Domínio</label>
                        <input type="text" class="form-control" name="dominio" id="dominio" value="{{ old('dominio', $empresa->dominio) }}">
                    </div>

                    <button type="submit" class="btn btn-outline-primary mt-3">Editar</button>
                </form>
            </div>
        </div>
    </div>
@endsection
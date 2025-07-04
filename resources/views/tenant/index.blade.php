@extends('dashboard')

@section('title', 'Empresa')

@section('content')
    <div class="container empresa" id="index">
        <div class="row">
            <div class="col-12 py-5">
                @if (session('success'))
                    <p class="alert alert-success">{{ session('success') }}</p>
                @endif

                <div class="header">
                    <h4>Empresas</h4>
                    <a href="{{ route('empresa.create') }}"><i class="fa-solid fa-plus me-2"></i>Cadastrar empresa</a>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Nome</th>
                                <th scope="col">Domínio</th>
                                <th scope="col">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($empresas as $index => $empresa)
                                <tr>
                                    <th scope="row">{{ $index + 1 }}</th>
                                    <td>{{ $empresa->nome }}</td>
                                    <td>{{ $empresa->dominio }}</td>
                                    <td class="d-flex gap-1 align-items-center">
                                        <a href="{{ route('empresa.edit', $empresa->id) }}" class="btn btn-warning">Editar</a>

                                        <form action="{{ route('empresa.destroy', $empresa->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="btn btn-danger">Excluir</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
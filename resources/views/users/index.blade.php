@extends('dashboard')

@section('title', 'Usuário')

@section('content')
    <div class="container usuario" id="index">
        <div class="row">
            <div class="col-12 py-5">
                @if (session('success'))
                    <p class="alert alert-success">{{ session('success') }}</p>
                @endif

                <div class="header">
                    <h4>Usuários</h4>
                    <a href="{{ route('usuario.create') }}"><i class="fa-solid fa-plus me-2"></i>Cadastrar usuário</a>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Nome</th>
                                <th scope="col">E-mail</th>
                                <th scope="col">Empresa</th>
                                <th scope="col">Função</th>
                                @if (auth()->user()->role === 'admin' || auth()->user()->role === 'superadmin')
                                    <th scope="col">Ações</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($usuarios as $index => $usuario)
                                @php
                                    $papel = match ($usuario->role) {
                                        'superadmin' => 'Administrador Mestre',
                                        'admin' => 'Administrador',
                                        'user' => 'Usuário'
                                    }
                                @endphp

                                <tr>
                                    <th scope="row">{{ $index + 1 }}</th>
                                    <td>{{ $usuario->name }}</td>
                                    <td>{{ $usuario->email }}</td>
                                    <td>{{ $usuario->tenant->nome }}</td>
                                    <td>{{ $papel }}</td>
                                    @if (auth()->user()->role === 'admin' || auth()->user()->role === 'superadmin')
                                        <td class="d-flex gap-1">
                                            <a href="{{ route('usuario.edit', $usuario->id) }}" class="btn btn-warning">Editar</a>

                                            <form action="{{ route('usuario.destroy', $usuario->id) }}" method="POST">
                                                @csrf
                                                @method('DELETE')

                                                <button type="submit" class="btn btn-danger">Excluir</button>
                                            </form>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
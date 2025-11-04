@extends('dashboard')

@section('title', 'Usuário')

@section('content')
    <div class="container-fluid usuario" id="index">
        <div class="row">
            <div class="col-12 py-5">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                    </div>
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
                                <th scope="col">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($usuarios as $index => $usuario)
                                @php
                                    $papel = match ($usuario->role) {
                                        'superadmin' => 'Administrador Mestre',
                                        'admin' => 'Liderança / Gestão',
                                        'user' => 'Colaborador'
                                    }
                                @endphp

                                <tr>
                                    <th scope="row">{{ $index + 1 }}</th>
                                    <td>{{ $usuario->name }}</td>
                                    <td>{{ $usuario->email }}</td>
                                    <td>{{ $usuario->tenant?->nome ?? 'Sem empresa' }}</td>
                                    <td>{{ $papel }}</td>
                                    <td class="d-flex gap-1">
                                        @if (
                                            auth()->user()->role === 'superadmin' ||
                                            (auth()->user()->role === 'admin' && auth()->user()->tenant_id === $usuario->tenant_id && $usuario->role !== 'superadmin') ||
                                            auth()->user()->id === $usuario->id
                                        )
                                            <a href="{{ route('usuario.edit', $usuario->id) }}" class="btn btn-warning btn-sm">Editar</a>
                                        @else
                                            Nenhum ação
                                        @endif

                                        @if (
                                            (auth()->user()->role === 'superadmin') ||
                                            (auth()->user()->role === 'admin' && auth()->user()->tenant_id === $usuario->tenant_id && $usuario->role !== 'superadmin')
                                        )
                                            <form action="{{ route('usuario.destroy', $usuario->id) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
        
                                                <button type="submit" class="btn btn-danger btn-sm">Excluir</button>
                                            </form>
                                        @endif
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
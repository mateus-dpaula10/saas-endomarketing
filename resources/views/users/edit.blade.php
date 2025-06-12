@extends('dashboard')

@section('title', 'Usuário')

@section('content')
    <div class="container usuario" id="edit">
        <div class="row">
            <div class="col-12 py-5">
                <div class="header">
                    <h4>Editar usuário '{{ $usuario->name }}'</h4>
                    <a href="{{ route('usuario.index') }}"><i class="fa-solid fa-arrow-left me-2"></i>Voltar</a>
                </div>

                <form action="{{ route('usuario.update', $usuario->id) }}" method="POST">
                    @csrf
                    @method('PATCH')

                    <div class="form-group mt-3">
                        <label for="name" class="form-label">Nome</label>
                        <input type="text" class="form-control" name="name" id="name" value="{{ old('name', $usuario->name) }}" required>
                    </div>
                    
                    <div class="form-group mt-3">
                        <label for="email" class="form-label">E-mail</label>
                        <input type="email" class="form-control" name="email" id="email" value="{{ old('email', $usuario->email) }}" required>
                    </div>

                    <div class="form-group mt-3">
                        <label for="password" class="form-label">Senha</label>
                        <input type="password" class="form-control" name="password" id="password" value="{{ old('password', $usuario->password) }}" required>
                    </div>

                    @if (auth()->user()->role === 'superadmin')
                        <div class="form-group mt-3">
                            <label for="tenant_id" class="form-label">Empresa</label>
                            <select name="tenant_id" id="tenant_id" class="form-select" required>
                                @foreach ($empresas as $empresa)
                                    <option value="{{ $empresa->id }}" {{ old('tenant_id', $usuario->tenant_id) == $empresa->id ? 'selected' : '' }}>
                                        {{ $empresa->nome }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group mt-3">
                            <label for="role" class="form-label">Papel</label>
                            <select name="role" id="role" class="form-select" required>
                                <option value="admin" {{ old('role', $usuario->role) == 'admin' ? 'selected' : '' }}>Admin</option>
                                <option value="user" {{ old('role', $usuario->role) == 'user' ? 'selected' : '' }}>Usuário</option>
                            </select>
                        </div>
                    @else
                        <input type="hidden" name="tenant_id" value="{{ auth()->user()->tenant_id }}">
                        <input type="hidden" name="role" value="{{ $usuario->role }}">
                    @endif

                    <button type="submit" class="btn btn-outline-primary mt-3">Editar</button>
                </form>
            </div>
        </div>
    </div>
@endsection
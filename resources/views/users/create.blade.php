@extends('dashboard')

@section('title', 'Usuário')

@section('content')
    <div class="container usuario" id="create">
        <div class="row">
            <div class="col-12 py-5">
                @if (session('success'))
                    <p class="alert alert-success">{{ session('success') }}</p>
                @endif

                <div class="header">
                    <h4>Criar usuário</h4>
                    <a href="{{ route('usuario.index') }}"><i class="fa-solid fa-arrow-left me-2"></i>Voltar</a>
                </div>

                @php
                    $authUser = auth()->user()
                @endphp

                <form action="{{ route('usuario.store') }}" method="POST">
                    @csrf

                    <div class="form-group mt-3">
                        <label for="name" class="form-label">Nome</label>
                        <input type="text" class="form-control" name="name" id="name" value="{{ old('name') }}" required>
                    </div>

                    <div class="form-group mt-3">
                        <label for="email" class="form-label">E-mail</label>
                        <input type="email" class="form-control" name="email" id="email" value="{{ old('email') }}" required>
                    </div>

                    <div class="form-group mt-3">
                        <label for="password" class="form-label">Senha</label>
                        <input type="password" class="form-control" name="password" id="password" value="{{ old('password') }}" required>
                    </div>

                    @if (auth()->user()->role === 'superadmin')
                        <div class="form-group mt-3">
                            <label for="tenant_id" class="form-label">Empresa</label>
                            <select name="tenant_id" id="tenant_id" class="form-select" required>
                                @foreach ($empresas as $empresa)
                                    <option value="{{ $empresa->id }}" {{ old('tenant_id') == $empresa->id ? 'selected' : '' }}>
                                        {{ $empresa->nome }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <input type="hidden" name="role" value="admin">

                        <button type="submit" class="btn btn-outline-primary mt-3">Cadastrar administrador</button>
                    @elseif (auth()->user()->role === 'admin')
                        <input type="hidden" name="tenant_id" value="{{ auth()->user()->tenant_id }}">
                        <input type="hidden" name="role" value="user">

                        <button type="submit" class="btn btn-outline-primary mt-3">Cadastrar usuário</button>
                    @endif
                </form>
            </div>
        </div>
    </div>
@endsection
@extends('dashboard')

@section('title', 'Usuário')

@section('content')
    <div class="container-fluid usuario" id="edit">
        <div class="row">
            <div class="col-12 py-5">
                <div class="header">
                    <h4>Editar usuário '{{ $usuario->name }}'</h4>
                    <a href="{{ route('usuario.index') }}"><i class="fa-solid fa-arrow-left me-2"></i>Voltar</a>
                </div>

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

                <form action="{{ route('usuario.update', $usuario->id) }}" method="POST">
                    @csrf
                    @method('PATCH')

                    <div class="form-group mt-3">
                        <label for="name" class="form-label">Nome</label>
                        <input type="text" class="form-control" name="name" id="name" value="{{ old('name', $usuario->name) }}" required>
                    </div>

                    @if (auth()->user()->role === 'superadmin' || (auth()->user()->role === 'admin' && auth()->user()->tenant_id === $usuario->tenant_id))
                        <div class="form-group mt-3">
                            <label for="email" class="form-label">E-mail</label>
                            <input type="email" class="form-control" name="email" id="email" value="{{ old('email', $usuario->email) }}" required>
                        </div>
                    @else
                        <div class="form-group mt-3">
                            <label for="email" class="form-label">E-mail</label>
                            <input type="email" class="form-control" name="email" id="email" value="{{ old('email', $usuario->email) }}" required readonly>
                        </div>
                    @endif    
                    
                    <div class="form-group mt-3">
                        <button type="button" id="generate-password" class="btn btn-secondary">Gerar senha forte</button>
                    </div>

                    <div class="form-group mt-3">
                        <label for="generated-password" class="form-label">Senha gerada (copiar):</label>
                        <div class="input-group">
                            <input type="text" id="generated-password" class="form-control" readonly>
                            <button type="button" id="copy-password" class="btn btn-primary">Copiar</button>
                        </div>
                    </div>

                    <div class="form-group mt-3">
                        <label for="password" class="form-label">Senha</label>
                        <input type="password" class="form-control" name="password" id="password" placeholder="Nova senha (opcional)">
                    </div>

                    <div class="form-group mt-3">
                        <label for="password_confirmation" class="form-label">Senha de confirmação</label>
                        <input type="password" class="form-control" name="password_confirmation" id="password_confirmation" placeholder="Confirme a nova senha (opcional)">
                    </div>

                    <div class="form-group mt-3">
                        <label for="password-strength">Força da senha</label>
                        <div id="password-strength" class="progress">
                            <div id="strength-bar" class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
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
                                <option value="admin" {{ old('role', $usuario->role) == 'admin' ? 'selected' : '' }}>Liderança / Gestão</option>
                                <option value="user" {{ old('role', $usuario->role) == 'user' ? 'selected' : '' }}>Colaborador</option>
                            </select>
                        </div>
                    @else
                        <input type="hidden" name="tenant_id" value="{{ auth()->user()->tenant_id }}">
                        <input type="hidden" name="role" value="{{ $usuario->role }}">
                    @endif

                    <button type="submit" class="btn btn-primary mt-3">Salvar</button>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const passwordInput = document.getElementById('password');
            const strengthBar = document.getElementById('strength-bar');
            const generateBtn = document.getElementById('generate-password');
            const generatedText = document.getElementById('generated-password');
            const copyBtn = document.getElementById('copy-password');

            const calculateStrength = (password) => {
                let score = 0;

                if (password.length >= 8) score += 20;

                if (/[A-Z]/.test(password)) score += 20;

                if (/[a-z]/.test(password)) score += 20;
                
                if (/[0-9]/.test(password)) score += 20;
                
                if (/[@$!%*?&]/.test(password)) score += 20;

                return score;
            }

            const updateStrengthBar = (password) => {
                const strength = calculateStrength(password);
                strengthBar.style.width = `${strength}%`;

                if (strength < 40) {
                    strengthBar.classList.remove('bg-success', 'bg-warning');
                    strengthBar.classList.add('bg-danger');
                } else if (strength >= 40 && strength < 80) {
                    strengthBar.classList.remove('bg-danger', 'bg-success');
                    strengthBar.classList.add('bg-warning');
                } else {
                    strengthBar.classList.remove('bg-danger', 'bg-warning');
                    strengthBar.classList.add('bg-success');
                }
            };

            passwordInput.addEventListener('input', function() {
                updateStrengthBar(passwordInput.value);
            });

            const gerarSenha = (tamanho = 12) => {
                const minusculas = 'abcdefghijklmnopqrstuvwxyz';
                const maiusculas = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                const numeros = '0123456789';
                const especiais = '@$!%*?&';
                
                let senha = minusculas[Math.floor(Math.random() * minusculas.length)]
                        + maiusculas[Math.floor(Math.random() * maiusculas.length)]
                        + numeros[Math.floor(Math.random() * numeros.length)]
                        + especiais[Math.floor(Math.random() * especiais.length)];

                const todos = minusculas + maiusculas + numeros + especiais;
                for (let i = senha.length; i < tamanho; i++) {
                    senha += todos[Math.floor(Math.random() * todos.length)];
                }

                return senha.split('').sort(() => 0.5 - Math.random()).join('');
            }

            generateBtn.addEventListener('click', () => {
                const novaSenha = gerarSenha();
                generatedText.value = novaSenha;
            });

            copyBtn.addEventListener('click', () => {
                generatedText.select();
                generatedText.setSelectionRange(0, 99999); 
                document.execCommand('copy');
                alert('Senha copiada para a área de transferência!');
            });
        })        
    </script>
@endpush
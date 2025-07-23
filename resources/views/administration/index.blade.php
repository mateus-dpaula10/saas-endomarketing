@extends('dashboard')

@section('title', 'Administração')

@section('content')
    <div class="container administration" id="index">
        <div class="row">
            <div class="col-12 py-5">
                <div class="header">
                    <h4>Painel administrativo</h4>
                    <a href="{{ route('usuario.index') }}"><i class="fa-solid fa-arrow-left me-2"></i>Voltar</a>
                </div>

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

                @if (auth()->user()->isAdmin())
                    @if(isset($user))
                        <h5 class="mb-4">Redefinir senha para o usuário '{{ $user->name }} - {{ $user->email }}'</h5>

                        <form action="{{ route('admin.reset.password', $user->id) }}" method="POST">
                            @csrf
                            <div class="form-group mb-3">
                                <label for="password">Nova senha</label>
                                <input type="password" name="password" id="password" class="form-control" required>
                            </div>
                            <div class="form-group mb-3">
                                <label for="password_confirmation">Senha de confirmação</label>
                                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
                            </div>
                            <div class="form-group mb-3">
                                <label for="password-strength">Força da senha</label>
                                <div id="password-strength" class="progress">
                                    <div id="strength-bar" class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                            <button class="btn btn-primary">Salvar</button>
                        </form>
                    @endif                
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const passwordInput = document.getElementById('password');
            const strengthBar = document.getElementById('strength-bar');

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
        })        
    </script>
@endpush
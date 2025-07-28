<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Saas Endomarketing - Login</title>
    <link rel="stylesheet" href="{{ asset('css/auth/style.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" 
        integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <link rel="icon" type="image/png" href="{{ asset('img/logos/ico-colorido.png') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" 
        integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" 
        crossorigin="anonymous" referrerpolicy="no-referrer">
    
</head>
<body>
    <div id="bloco-login">
        <div>
            <a href="{{ route('index') }}">
                <img src="{{ asset('img/logos/branco.png') }}" alt="Logo do sistema Saas">
                <p>Diagnóstico, plano de ação e campanhas para engajar sua equipe.</p>
            </a>
        </div>

        <div>
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

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                </div>
            @endif

            <h2></h2>

            <form id="form-login" method="POST" action="{{ route('login.login') }}">
                @csrf
                
                <div class="form-group">
                    <label for="email" class="form-label">E-mail</label>
                    <input type="email" name="email" id="email" class="form-control">
                </div>

                <div class="form-group mt-4">
                    <label for="password" class="form-label">Senha</label>
                    <div class="position-relative">
                        <input type="password" name="password" id="password" class="form-control">
                        <i class="bi bi-eye-fill" id="icon_fa_eye" title="Mostrar senha"></i>
                    </div>
                </div>

                <button class="mt-4" type="submit" class="">Entrar</button>

                <a class="mt-3" href="#" id="esqueceu-senha-login">Esqueceu a senha?</a>
            </form>

            {{-- <form id="form-esqueceu-senha" method="POST" action="{{ route('password.reset') }}">
                @csrf
                
                <div class="form-group">
                    <label for="email" class="form-label">E-mail</label>
                    <input type="email" name="email" id="email" class="form-control">
                </div>

                <button class="mt-4" type="submit" class="">Enviar link de recuperação</button>

                <a class="mt-3" href="#" id="voltar-login">Voltar ao login</a>
            </form> --}}
        </div>
    </div>

    <script src="{{ asset('js/auth/script.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" 
        integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/js/all.min.js" 
        integrity="sha512-b+nQTCdtTBIRIbraqNEwsjB6UvL3UEMkXnhzd8awtCYh0Kcsjl9uEgwVFVbhoj3uu1DO1ZMacNvLoyJJiNfcvg==" 
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
</body>
</html>
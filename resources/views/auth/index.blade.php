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
    <link rel="icon" type="image/png" href="{{ asset('img/logos/sistema-favicon.png') }}">
</head>
<body>
    <div id="bloco-login">
        <div>
            <a href="{{ route('index') }}">
                <img src="{{ asset('img/logos/sistema.png') }}" alt="Logo do sistema Saas">
                <p>Diagnóstico, plano de ação e campanhas para engajar sua equipe.</p>
            </a>
        </div>

        <div>
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
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
                    <input type="password" name="password" id="password" class="form-control">
                </div>

                <button class="mt-4" type="submit" class="">Entrar</button>

                <a class="mt-3" href="#" id="esqueceu-senha-login">Esqueceu a senha?</a>
            </form>

            <form id="form-esqueceu-senha" method="POST">
                @csrf
                
                <div class="form-group">
                    <label for="email" class="form-label">E-mail</label>
                    <input type="email" name="email" id="email" class="form-control">
                </div>

                <button class="mt-4" type="submit" class="">Enviar link de recuperação</button>

                <a class="mt-3" href="#" id="voltar-login">Voltar ao login</a>
            </form>
        </div>
    </div>

    <script src="{{ asset('js/auth/script.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" 
        integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>
</body>
</html>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Saas Endomarketing - @yield('title')</title>
    <link rel="stylesheet" href="{{ asset('css/dashboard/style.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" 
        integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" 
        integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" 
        crossorigin="anonymous" referrerpolicy="no-referrer">
    <link rel="icon" type="image/png" href="{{ asset('img/logos/ico-colorido.png') }}">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
</head>
<body>  
    <aside>
        <button id="close-menu">☰</button>

        <a href="{{ route('dashboard.index') }}">
            <img src="{{ asset('img/logos/branco.png') }}" alt="Logo do sistema Saas">
        </a>

        <nav>
            <ul>
                @auth
                    <li style="cursor: default">Usuário logado: {{ auth()->user()->name }}</li>
                    <li><a href="{{ route('dashboard.index') }}">Dashboard</a></li>
                    <li><a href="{{ route('usuario.index') }}">Usuários</a></li>
                    <li><a href="{{ route('diagnostico.index') }}">Diagnósticos</a></li>
                @endauth
                @if(auth()->user()->role === 'superadmin')
                    <li><a href="{{ route('empresa.index') }}">Empresas</a></li>
                    <li><a href="{{ route('campanha.index') }}">Campanhas</a></li>
                @endif
                <li>
                    <a href="#" onclick="event.preventDefault(); document.getElementById('form-logout').submit()">      
                        Sair            
                    </a>
                </li>
                <form method="POST" action="{{ route('login.logout') }}" style="display: none" id="form-logout">
                    @csrf
                </form>    
            </ul>
        </nav>
    </aside>

    <main>
        <div id="barra-notificacao">
            
        </div>

        @yield('content')
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const aside = document.querySelector('aside')
            const toggleAside = document.querySelector('#close-menu')

            toggleAside.addEventListener('click', () => {
                aside.classList.toggle('opened');
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" 
        integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/js/all.min.js" 
        integrity="sha512-b+nQTCdtTBIRIbraqNEwsjB6UvL3UEMkXnhzd8awtCYh0Kcsjl9uEgwVFVbhoj3uu1DO1ZMacNvLoyJJiNfcvg==" 
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    @stack('scripts')
</body>
</html>
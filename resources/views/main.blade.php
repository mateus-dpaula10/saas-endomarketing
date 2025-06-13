<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Saas Endomarketing - @yield('title')</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" 
        integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" 
        integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" 
        crossorigin="anonymous" referrerpolicy="no-referrer">
    <link rel="icon" type="image/png" href="{{ asset('img/logos/sistema-favicon.png') }}">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
</head>
<body>
    <header class="py-2">
        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <a class="navbar-brand" href="{{ route('index') }}">
                    <img src="{{ asset('img/logos/sistema.png') }}" alt="Logo do sistema Saas">
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                    {{-- <span></span>
                    <span></span>
                    <span></span> --}}
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="{{ route('index') }}">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/#problemas">Problemas</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/#como-funciona">Como funciona</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/#resultados">Resultados</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/#planos">Planos</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">Comece Agora</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <main>
        @yield('content')
    </main>

    <footer class="pt-5 pb-3">
        <div class="container">
            <div class="row" data-aos="fade-in-up" data-aos-duration="1000">
                <div class="col-12" id="conteudo-footer">
                    <div>
                        <a href="#">
                            <img src="{{ asset('img/logos/sistema.png') }}" alt="Logo do sistema Saas">
                        </a>
                        <p>Diagnóstico, plano de ação e campanhas para engajar sua equipe.</p>
                    </div>
                    <div>
                        <h5>Sistema</h5>
                        <ul>
                            <li><a href="#como-funciona">Como funciona</a></li>
                            <li><a href="#resultados">Resultados</a></li>
                            <li><a href="#planos">Planos</a></li>
                        </ul>
                    </div>
                    <div>
                        <h5>Empresa</h5>
                        <ul>
                            <li><a href="https://hiatoconteudodigital.com.br/#porquehiato">Sobre</a></li>
                            <li><a href="https://hiatoconteudodigital.com.br/vem-pra-ca/#vempraca">Contato</a></li>
                        </ul>
                    </div>
                    <div>
                        <h5>Legal</h5>
                        <ul>
                            <li><a href="#">Termos de uso</a></li>
                            <li><a href="#">Política de privacidade</a></li>
                        </ul>
                    </div>
                </div>

                <div class="col-12 mt-5">
                    <p class="text-center mb-0">Copyright &copy; <?php echo date('Y')?> - Todos os direitos reservados</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="{{ asset('js/script.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" 
        integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/js/all.min.js" 
        integrity="sha512-b+nQTCdtTBIRIbraqNEwsjB6UvL3UEMkXnhzd8awtCYh0Kcsjl9uEgwVFVbhoj3uu1DO1ZMacNvLoyJJiNfcvg==" 
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    @stack('scripts')
</body>
</html>
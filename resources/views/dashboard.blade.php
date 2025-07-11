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
    <link rel="icon" type="image/png" href="{{ asset('img/logos/sistema-favicon.png') }}">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <aside>
        <button id="close-menu">☰</button>

        <a href="{{ route('dashboard.index') }}">
            <img src="{{ asset('img/logos/sistema.png') }}" alt="Logo do sistema Saas">
        </a>

        <nav>
            <ul>
                @if (auth()->user())
                    <li>Usuário logado: {{ auth()->user()->name }}</li>
                @endif
                @if(auth()->user())
                    <li><a href="{{ route('dashboard.index') }}">Dashboard</a></li>
                @endif
                @if(auth()->user()->role === 'superadmin')
                    <li><a href="{{ route('empresa.index') }}">Empresas</a></li>
                @endif
                @if(auth()->user())
                    <li><a href="{{ route('usuario.index') }}">Usuários</a></li>
                @endif
                @if(auth()->user())
                    <li><a href="{{ route('diagnostico.index') }}">Diagnósticos</a></li>
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
        @php
            $notificationCount = count($notifications ?? []);
        @endphp

        <div id="barra-notificacao">
            <div class="position-relative" id="notification"> 
                <i class="fa-regular fa-bell {{ count($notifications ?? []) > 0 ? 'bell-shake' : '' }}" id="icon-notification" title="Notificações"></i>
                @if($notificationCount > 0)
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        {{ $notificationCount }}
                        <span class="visually-hidden">notificações não lidas</span>
                    </span>
                @endif
            </div>            
        </div>

        <div class="modal fade" id="notificationsModal" tabindex="-1" aria-labelledby="notificationsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="notificationsModalLabel">Notificações de Diagnósticos Abertos</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <div class="modal-body" id="notificationsContent"></div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    </div>
                </div>
            </div>
        </div>

        @yield('content')
    </main>

    <script>
        const notifications = @json($notifications ?? []);

        function renderNotifications() {
            const container = document.getElementById('notificationsContent');
            container.innerHTML = '';

            if (!notifications.length) {
                container.innerHTML = '<p class="mb-0">Nenhum diagnóstico aberto para resposta.</p>';
                return;
            }

            function parseDateToLocal(dateString) {
                const parts = dateString.split('-');
                return new Date(parts[0], parts[1] - 1, parts[2], 12, 0, 0);
            }

            notifications.forEach(notif => {
                const daysLeft = notif.days_left;
                const date = parseDateToLocal(notif.deadline);
                const formattedDate = date.toLocaleDateString('pt-BR');
                const urgency = daysLeft <= 3 ? 'alert-danger' : 'alert-warning';

                let html = `
                    <div class="notification-item mb-3 p-2 border rounded alert ${urgency}">
                        <h6>${notif.title}</h6>
                        <p>
                            Prazo para resposta: 
                            <strong>${formattedDate}</strong> (${daysLeft} dia${daysLeft !== 1 ? 's' : ''} restante${daysLeft !== 1 ? 's' : ''})
                        </p>
                `;

                container.innerHTML += html;
            });
        }

        document.getElementById('notification').addEventListener('click', () => {
            renderNotifications();
            const modal = new bootstrap.Modal(document.getElementById('notificationsModal'));
            modal.show();
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" 
        integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>
    <script src="{{ asset('js/dashboard/script.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/js/all.min.js" 
        integrity="sha512-b+nQTCdtTBIRIbraqNEwsjB6UvL3UEMkXnhzd8awtCYh0Kcsjl9uEgwVFVbhoj3uu1DO1ZMacNvLoyJJiNfcvg==" 
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    @stack('scripts')
</body>
</html>
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

                @if (auth()->user()->isAdmin())
                    @php
                        $resetRequests = \Illuminate\Notifications\DatabaseNotification::where('notifiable_id', auth()->id())
                            ->where('notifiable_type', \App\Models\User::class)
                            ->whereJsonContains('data->title', 'Redefinição de senha solicitada')
                            ->orderByDesc('created_at')
                            ->get();
                    @endphp

                    @if($resetRequests->count())
                        <h4>Solicitações de redefinição de senha</h4>
                        @foreach($resetRequests as $request)
                            <div class="alert alert-warning mb-3">
                                <p>{{ $request->data['message'] }}</p>
                                <a href="{{ $request->data['link'] }}" class="btn btn-sm btn-warning">Redefinir Senha</a>
                                <small class="text-muted d-block mt-1">Recebido em: {{ \Carbon\Carbon::parse($request->created_at)->format('d/m/Y H:i') }}</small>
                            </div>
                        @endforeach
                    @endif
                @endif

                {{-- @isset($user)
                    <h3>Redefinir senha para: {{ $user->name }} {{ $user->email }}</h3>

                    <form action="{{ route('admin.reset.password') }}" method="POST">
                        @csrf

                        <div class="form-group">
                            <input type="password" name="password" placeholder="Nova senha" class="form-control">
                            <input type="password" name="password_confirmation" placeholder="Confirmar nova senha" class="form-control">
                            <button type="submit" class="btn btn-primary">Salvar</button>
                        </div>
                    </form>
                @else
                    <h5>Bem vindo ao painel administrativo!</h5>
                @endisset --}}
            </div>
        </div>
    </div>
@endsection
@extends('dashboard')

@section('title', 'Dashboard')

@section('content')
    <div class="container-fluid dashboard" id="index">
        <div class="row">
            <div class="col-12 py-5">                
                <h5 class="mb-0">Bem vindo '{{ $authUser->name }}'</h5>

                @if (in_array($authUser->role, ['admin', 'user']))
                    <div class="alert alert-info mt-4">
                        @if ($pendingCount > 0)
                            <h6>Você tem {{ $pendingCount }} diagnóstico(s) pendente(s) para responder.</h6>
                            <ul class="mb-0 mt-3">
                                @foreach ($pendingDiagnostics as $diagnostic)
                                    <li>
                                        <a href="{{ route('diagnostico.answer', $diagnostic->id) }}">
                                            {{ $diagnostic->title ?? 'Diagnóstico sem título' }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            Você não possui diagnóstico(s) pendente(s).
                        @endif                    
                    </div>                    
                @endif

                <div class="card mt-4">
                    <div class="card-body">
                        <h5 class="card-title">
                            @if($authUser->role === 'superadmin')
                                Saúde geral das empresas
                            @else
                                Saúde da empresa
                            @endif
                        </h5>
                        <p class="card-text">
                            Índice de saúde baseado nas respostas dos diagnósticos: <strong>{{ $healthIndex }}%</strong>
                        </p>
                        <div class="progress">
                            <div 
                                class="progress-bar" 
                                role="progressbar" 
                                style="width: {{ $healthIndex }}%;" 
                                aria-valuenow="{{ $healthIndex }}" 
                                aria-valuemin="0" 
                                aria-valuemax="100"
                            ></div>
                        </div>
                    </div>              
                </div>

                @if($authUser->role === 'admin' && $pendingUsers->isNotEmpty())
                    <div class="card mt-4">
                        <div class="card-body">
                            <h5>Usuários com diagnósticos pendentes:</h5>
                            <ul class="mb-0 mt-3">
                                @foreach($pendingUsers as $user)
                                    <li>
                                        <strong>{{ $user['user']->name }}</strong> 
                                        - {{ $user['pendingCount'] }} diagnóstico(s) pendente(s)
                                        <ul class="mt-2">
                                            @foreach($user['pendingDiagnostics'] as $diag)
                                                <li>{{ $diag->title ?? 'Sem título' }}</li>
                                            @endforeach
                                        </ul>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
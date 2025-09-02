@extends('dashboard')

@section('title', 'Dashboard')

@section('content')
    <div class="container-fluid dashboard" id="index">
        <div class="row">
            <div class="col-12 py-5">                
                <div class="alert alert-info alert-dismissible fade show mt-4" role="alert">
                    {{ $mensagem ?? 'Bem-vindo!' }}
                    <p class="mb-0">Aqui você poderá acompanhar campanhas, lembretes da empresa ou seus próximos diagnósticos.</p>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                </div>
            </div>
        </div>
    </div>
@endsection
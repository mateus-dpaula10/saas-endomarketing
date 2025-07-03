@extends('dashboard')

@section('title', 'Dashboard')

@section('content')
    <div class="container dashboard" id="index">
        <div class="row">
            <div class="col-12 py-5">
                @if(isset($semRespostas) && $semRespostas)
                    <div class="alert alert-warning mt-4">
                        Nenhuma resposta registrada ainda para gerar comparação dos diagnósticos.
                    </div>
                @elseif(isset($analisesPorEmpresa) && count($analisesPorEmpresa))
                    @include('dashboard._superadmin')
                @elseif(isset($evolucaoCategorias))
                    @include('dashboard._admin')
                @else
                    @include('dashboard._user')
                @endif
            </div>
        </div>
    </div>
@endsection
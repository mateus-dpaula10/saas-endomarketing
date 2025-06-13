@extends('dashboard')

@section('title', 'Diagnóstico')

@section('content')
    <div class="container diagnostico" id="index">
        <div class="row">
            <div class="col-12 py-5">
                @if (session('success'))
                    <p class="alert alert-success">{{ session('success') }}</p>
                @endif

                <div class="header">
                    <h4>Diagnósticos</h4>
                    @if (auth()->user()->role === 'superadmin')
                        <a href="{{ route('diagnostico.create') }}"><i class="fa-solid fa-plus me-2"></i>Cadastrar diagnóstico</a>
                    @endif
                </div>

                @if ($diagnostics->isEmpty())
                    <p class="mb-0 bg-secondary p-3 text-white rounded-1">Nenhum diagnóstico cadastrado.</p>
                @else
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Título</th>
                                    <th>Descrição</th>
                                    <th>Criado em</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($diagnostics as $diagnostic)
                                    <tr>
                                        <td>{{ $diagnostic->title }}</td>
                                        <td>{{ $diagnostic->description }}</td>
                                        <td>{{ $diagnostic->created_at->format('d/m/Y') }}</td>
                                        <td>
                                            @if (auth()->user()->role === 'admin')
                                                <a href="{{ route('diagnostico.answer', $diagnostic->id) }}" class="btn btn-primary">Responder</a> 
                                            @endif

                                            @if (auth()->user()->role === 'superadmin')
                                                <a href="{{ route('diagnostico.edit', $diagnostic->id) }}" class="btn btn-warning">Editar</a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
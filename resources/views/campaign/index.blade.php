@extends('dashboard')

@section('title', 'Campanha')

@section('content')
    <div class="container-fluid campanha" id="index">
        <div class="row">
            <div class="col-12 py-5">     
                <div class="header">
                    <h4>Campanhas</h4>
                    <a href="{{ route('campanha.create') }}"><i class="fa-solid fa-plus me-2"></i>Cadastrar campanha</a>
                </div>        

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                    </div>
                @endif

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

                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Título</th>
                                <th scope="col">Categoria</th>
                                <th scope="col">Empresa</th>
                                <th scope="col">Ativa</th>
                                <th scope="col">Conteúdos</th>
                                <th scope="col">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($campaigns as $index => $campaign)
                                <tr>
                                    <th>{{ $index + 1 }}</th>
                                    <td>{{ $campaign->title }}</td>
                                    <td>{{ $campaign->category?->name ?? 'Sem categoria' }}</td>
                                    <td>
                                        @foreach ($campaign->tenants as $tenant)
                                            {{ $tenant->nome }}<br>
                                        @endforeach
                                    </td>
                                    <td>{{ $campaign->active ? 'Sim' : 'Não' }}</td>
                                    <td>
                                        @foreach ($campaign->contents as $content)
                                            <div class="mb-1">
                                                <strong>{{ ucfirst($content->type) }}:</strong>
                                                @if (in_array($content->type, ['text', 'link']))
                                                    {{ $content->content }}
                                                @else
                                                    <a href="{{ asset('storage/' . $content->file_path) }}" target="_blank">Ver arquivo</a>
                                                @endif
                                            </div>
                                        @endforeach
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('campanha.edit', $campaign->id) }}" class="btn btn-warning btn-sm">Editar</a>
                                            <form action="{{ route('campanha.destroy', $campaign->id) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">Excluir</button>
                                            </form>                                        
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">Nenhuma campanha cadastrada.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
@extends('dashboard')

@section('title', 'Dashboard')

@section('content')
    <div class="container dashboard" id="index">
        <div class="row">
            <div class="col-12 py-5">
                @php
                    $role = Auth::user()->role;
                @endphp
                
                @if($role === 'superadmin' && isset($analisesPorEmpresa) && count($analisesPorEmpresa))
                    @include('dashboard._superadmin')
                @elseif($role === 'admin' && isset($evolucaoCategorias))
                    @include('dashboard._admin')
                @elseif($role === 'user')
                    @include('dashboard._user')
                @elseif(isset($semRespostas) && $semRespostas)
                    <div class="alert alert-warning mt-4">
                        Nenhuma resposta registrada ainda para gerar comparação dos diagnósticos.
                    </div>
                @else
                    <div class="alert alert-info mt-4">Bem-vindo! Dados ainda não disponíveis.</div>
                @endif

                @if(isset($campanhas) && $campanhas->isNotEmpty())
                    <div class="mt-5">
                        <h4 class="mb-3">Campanhas em andamento</h4>
                        <ul class="list-group">
                            @foreach($campanhas as $campanha)
                                <li class="list-group-item mt-2" data-bs-toggle="modal" data-bs-target="#campanhaModal{{ $campanha->id }}" style="cursor: pointer">
                                    <strong>{{ $campanha->text }}</strong><br>
                                    {{ $campanha->description }}<br>
                                    <small>
                                        Vigência:
                                        {{ \Carbon\Carbon::parse($campanha->start_date)->format('d/m/Y') }}
                                        até
                                        {{ \Carbon\Carbon::parse($campanha->end_date)->format('d/m/Y') }}
                                    </small>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@foreach($campanhas as $campanha)
    @php
        $conteudo = $campanha->standardCampaign->content ?? null;
    @endphp
    
    <div class="modal fade" id="campanhaModal{{ $campanha->id }}" tabindex="-1" aria-labelledby="campanhaModalLabel{{ $campanha->id }}" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="campanhaModalLabel{{ $campanha->id }}">{{ $campanha->text }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Descrição:</strong> {{ $campanha->description }}</p>

                    @if($conteudo && $conteudo->goal)
                        <p><strong>Objetivo:</strong> {{ $conteudo->goal }}</p>
                    @endif

                    @if($conteudo && $conteudo->video_url)
                        <div class="ratio ratio-16x9 mb-3">
                            <iframe src="{{ $conteudo->video_url }}" title="Vídeo da campanha" allowfullscreen></iframe>
                        </div>
                    @endif

                    @if($conteudo && $conteudo->image_url)
                        <img src="{{ asset($conteudo->image_url) }}" alt="Imagem da campanha" class="img-fluid mb-3">
                    @endif

                    @if($conteudo && !empty($conteudo->actions))
                        <h6>Ações sugeridas:</h6>
                        <ul>
                            @foreach($conteudo->actions as $acao)
                                <li>{{ $acao }}</li>
                            @endforeach
                        </ul>
                    @endif

                    @if($conteudo && !empty($conteudo->resources))
                        <h6>Materiais:</h6>
                        <ul>
                            @foreach($conteudo->resources as $material)
                                <li><a href="{{ asset($material) }}" target="_blank">Abrir material</a></li>
                            @endforeach
                        </ul>
                    @endif

                    @if($conteudo && !empty($conteudo->quiz))
                        <h6>Reflexões:</h6>
                        <ul>
                            @foreach($conteudo->quiz as $pergunta)
                                <li>{{ $pergunta }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>
@endforeach
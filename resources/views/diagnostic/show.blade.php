@extends('dashboard')

@section('title', 'Diagnóstico')

@section('content')
    <div class="container-fluid diagnostico" id="show">
        <div class="row">
            <div class="col-12 py-5">
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

                <div class="header d-flex justify-content-between align-items-center mb-5">
                    <h4>Visualizar resultado do diagnóstico '{{ $diagnostic->title }}'</h4>
                    <a href="{{ route('diagnostico.index') }}"><i class="fa-solid fa-arrow-left me-2"></i>Voltar</a>
                </div>
                
                <div>                
                    @php
                        $roles = ['admin' => 'Liderança / Gestão', 'user' => 'Colaboradores'];
                    @endphp

                    @foreach ($roles as $roleKey => $roleLabel)
                        @php
                            $resultado = $data['analisePorRole'][$roleKey] ?? null;
                            $resumoRole = $data['resumoPorRole'][$roleKey] ?? null;
                        @endphp

                        <div class="mb-5">
                            <h4 class="mb-3 text-center">📊 {{ $roleLabel }}</h4>                                                            

                            @if ($resultado && !empty($resultado['classificacao']))
                                <div class="row row-cols-1 row-cols-md-2 g-3">
                                    @foreach (['predominante', 'secundario', 'fraco', 'ausente'] as $status)
                                        @php
                                            $quadrantes = $resultado['classificacao'][$status] ?? [];
                                            $badgeClass = match($status) {
                                                'predominante' => '#198754',
                                                'secundario'   => '#D1E7DD',
                                                'fraco'        => '#F8D7DA',
                                                'ausente'      => '#DC3545',
                                                default        => 'bg-light'
                                            };
                                            $textColor = match($status) {
                                                'secundario'   => '#0a3622',
                                                'fraco'        => '#58151c', 
                                                default        => '#FFF'
                                            };

                                            $descricao = match($status) {
                                                'predominante' => 'Cultura predominante — representa o quadrante mais forte na organização.',
                                                'secundario'   => 'Traços secundários — influenciam, mas não definem o perfil dominante.',
                                                'fraco'        => 'Cultura fraca ou ausente — pouco presente nas práticas organizacionais.',
                                                'ausente'      => 'Cultura ausente — praticamente inexistente no ambiente atual.',
                                                default        => ''
                                            }
                                        @endphp

                                        @foreach ($quadrantes as $quadrante)
                                            <div class="col">
                                                <div class="card h-100" style="background-color: {{ $badgeClass }}; color: {{ $textColor }}">
                                                    <div class="card-body">
                                                        <h5 class="card-title">
                                                            {{ $quadrante }}
                                                        </h5>
                                                        <h6 class="card-text mb-2">
                                                            {{ $descricao }}
                                                        </h6>
                                                        <p class="card-text mb-1">
                                                            Média: <strong>{{ $resultado['medias'][$quadrante] ?? 'N/A' }}</strong>
                                                        </p>
                                                        <small class="card-text mb-1">
                                                            Ideal: {{ $culturaContexto[$quadrante]['ideal'] ?? 'N/A' }}
                                                        </small> <br>
                                                        <small class="card-text mb-1">
                                                            Evitar: {{ $culturaContexto[$quadrante]['evitar'] ?? 'N/A' }}
                                                        </small>
                                                        <p class="card-text mt-2 mb-0">
                                                            {{ $resultado['sinais'][$quadrante] ?? 'Sem sinais suficientes.' }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endforeach
                                </div>
                            @else
                                <div class="alert alert-warning text-center">
                                    Sem dados suficientes para análise de {{ $roleLabel }}.
                                </div>
                            @endif

                            @if ($resumoRole)
                                <div class="alert alert-info mt-3">
                                    {{ $resumoRole }}
                                </div>
                            @endif
                        </div>
                    @endforeach

                    @if(!empty($data['resumoGeral']))
                        <div class="mb-5">
                            <h4 class="mb-3 text-center">📌 Resumo Geral da Organização</h4>
                            <div class="alert alert-success">
                                {{ $data['resumoGeral'] }}
                            </div>
                        </div>
                    @endif
                    
                    {{-- @if (!empty($data['comparativoRoles']) && count($data['comparativoRoles']) > 0)
                        <div class="mb-5">
                            <h4 class="mb-3 text-center">🔍 Comparativo entre Colaboradores e Gestão</h4>

                            <div class="table-responsive">
                                <table class="table table-bordered align-middle">
                                    <thead class="table-light">
                                        <tr class="text-center">
                                            <th style="width: 30%">Elemento</th>
                                            <th style="width: 35%">Colaboradores</th>
                                            <th style="width: 35%">Gestão / Liderança</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($data['comparativoRoles'] as $comparativo)
                                            @php
                                                $label = match($comparativo['elemento'] ?? $comparativo->elemento ?? '') {
                                                    'cultura_predominante' => 'Cultura Predominante',
                                                    'reconhecimento' => 'Reconhecimento',
                                                    'comunicacao' => 'Comunicação',
                                                    'lideranca' => 'Liderança',
                                                    'comprometimento' => 'Comprometimento',
                                                    'aspiracao' => 'Aspiração',
                                                    default => ucfirst(str_replace('_', ' ', $comparativo['elemento'] ?? $comparativo->elemento ?? ''))
                                                };
                                            @endphp

                                            <tr>
                                                <td class="fw-bold">{{ $label }}</td>
                                                <td>{{ $comparativo['colaboradores'] ?? $comparativo->colaboradores ?? '-' }}</td>
                                                <td>{{ $comparativo['gestao'] ?? $comparativo->gestao ?? '-' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif --}}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        
    </script>
@endpush
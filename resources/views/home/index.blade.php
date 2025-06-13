@extends('main')

@section('title', 'Home')

@section('content')
    <div id="topo">
        <div data-aos="fade-up" data-aos-duration="1000">
            <h1>
                Otimize seu Endomarketing com IA e Engaje seus Colaboradores!
            </h1>
            <p>
                Sistema completo que analisa o cenário de endomarketing da sua empresa 
                e gera planos de ação e campanhas mensais personalizadas.
            </p>
            <a href="#">
                Comece Agora
            </a>
        </div>
    </div>

    <div id="problemas">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6" data-aos="fade-right" data-aos-duration="1000">
                    <h5>O Problema que Resolvemos</h5>
                    <h1>
                        Sua Comunicação Interna Não está Engajando?
                    </h1>
                    <ul class="mt-3">
                        <li>
                            Falta de engajamento dos funcionários;
                        </li>
                        <li>
                            Comunicação desalinhada;
                        </li>
                        <li>
                            Alto turnover;
                        </li>
                        <li>
                            Dificuldade em medir resultados do endomarketing;
                        </li>
                        <li>
                            Perda de talentos.
                        </li>
                    </ul>
                </div>

                <div class="col-lg-6 mt-3 mt-lg-0" data-aos="fade-left" data-aos-duration="1000">                    
                    <div id="bloco-img-problemas">
                        <img src="{{ asset('img/home/problemas.avif') }}" alt="Imagem do bloco problemas">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="como-funciona">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h5>Como Funciona?</h5>
                    <h1>
                        Nossa Metodologia Inteligente
                    </h1>

                    <div class="row row-cols-1 row-cols-lg-3">
                        <div class="col" data-aos="fade-right" data-aos-duration="1000">
                            <div class="cardd">
                                <i class="fa-solid fa-list-check"></i>
                                <h4>Diagnóstico com IA</h4>
                                <p>
                                    Responda ao nosso questionário inteligente sobre sua cultura, equipe e 
                                    canais de comunicação.
                                </p>
                            </div>
                        </div>
                        <div class="col mt-3 mt-lg-0" data-aos="fade-up" data-aos-duration="1000">
                            <div class="cardd">
                                <i class="fa-solid fa-chart-bar"></i>
                                <h4>Relatório e Plano de Ação Personalizado</h4>
                                <p>
                                    Nossa IA analisa suas respostas e gera um relatório detalhado com diagnóstico, 
                                    plano de ação estratégico e um manual dos canais ideais.
                                </p>
                            </div>
                        </div>
                        <div class="col mt-3 mt-lg-0" data-aos="fade-left" data-aos-duration="1000">
                            <div class="cardd">
                                <i class="fa-solid fa-bullhorn"></i>
                                <h4>Campanhas Mensais Estratégicas</h4>
                                <p>
                                    Receba campanhas de endomarketing prontas, criadas mensalmente com base no seu 
                                    calendário e objetivos.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="resultados">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h5>Resultados que Você Vai Alcançar</h5>
                    <h1>
                        O Que Você Ganha ao Usar Nossa Plataforma?
                    </h1>
                    <p>
                        Com o diagnóstico preciso da nossa Inteligência Artificial e campanhas estratégicas personalizadas, 
                        sua empresa está prestes a colher frutos que impactam diretamente o sucesso e o bem-estar de todos.
                    </p>

                    <div id="bloco-cardd_">
                        <div class="cardd_" data-aos="flip-right" data-aos-duration="1000">
                            <div>
                                <i class="fa-solid fa-handshake"></i>
                                <h3>Engajamento Disparado</h3>
                            </div>
                            <p>
                                Veja seus colaboradores mais motivados, proativos e verdadeiramente conectados aos objetivos da empresa. 
                                Crie uma cultura de pertencimento onde cada voz importa.
                            </p>
                        </div>
    
                        <div class="cardd_" data-aos="flip-left" data-aos-duration="1000">
                            <div>
                                <i class="fa-solid fa-building"></i>
                                <h3>Cultura Fortalecida</h3>
                            </div>
                            <p>
                                Alinhe sua equipe aos valores e à missão da organização, construindo um ambiente de trabalho mais coeso, 
                                positivo e produtivo.
                            </p>
                        </div>
    
                        <div class="cardd_" data-aos="flip-right" data-aos-duration="1000">
                            <div>
                                <i class="fa-solid fa-award"></i>
                                <h3>Retenção de Talentos</h3>
                            </div>
                            <p>
                                Reduza o turnover e mantenha seus melhores profissionais. Funcionários engajados e valorizados são mais 
                                leais e menos propensos a buscar novas oportunidades.
                            </p>
                        </div>

                        <div class="cardd_" data-aos="flip-left" data-aos-duration="1000">
                            <div>
                                <i class="fa-solid fa-bullhorn"></i>
                                <h3>Comunicação Sem Ruídos</h3>
                            </div>
                            <p>
                                Garanta que as mensagens certas cheguem às pessoas certas, na hora certa, eliminando mal-entendidos e 
                                fortalecendo a transparência em todos os níveis.
                            </p>
                        </div>

                        <div class="cardd_" data-aos="flip-up" data-aos-duration="1000">
                            <div>
                                <i class="fa-solid fa-rocket"></i>
                                <h3>Produtividade em Alta</h3>
                            </div>
                            <p>
                                Colaboradores bem informados, engajados e com propósito definido são mais eficientes, colaborativos e 
                                focados na entrega de resultados excepcionais.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="planos">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h5>Planos e Preços</h5>
                    <h1>
                        Escolha o Plano Ideal para sua Empresa
                    </h1>

                    <div class="row row-cols-1 row-cols-lg-3">
                        <div class="col" data-aos="zoom-in-right" data-aos-duration="1000">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="card-header">
                                        <h3>Starter</h3>
                                    </div>
                                    <div class="card-content">
                                        <p class="plan-type">👥 Pequenas equipes</p>
                                        <small class="subtitle">Inclui:</small>
                                        <ul class="benefits">
                                            <li>✅ 1 diagnóstico</li>
                                            <li>👤 Até 20 colaboradores</li>
                                            <li>📢 5 campanhas por mês</li>
                                        </ul>
                                    </div>
                                    <button class="btn btn-primary">Valor Sob Consulta</button>
                                </div>
                            </div>
                        </div>

                        <div class="col mt-3 mt-lg-0" data-aos="zoom-in-up" data-aos-duration="1000">
                            <div class="card h-100 destaque">
                                <div class="card-body">
                                    <div class="card-header">
                                        <h3>Pro</h3>
                                        <span class="badge">Mais Popular</span>
                                    </div>
                                    <div class="card-content">
                                        <p class="plan-type">🏢 Empresas médias</p>
                                        <small class="subtitle">Inclui:</small>
                                        <ul class="benefits">
                                            <li>✅ Diagnóstico completo</li>
                                            <li>👥 Até 100 colaboradores</li>
                                            <li>📢 20 campanhas por mês</li>
                                        </ul>
                                    </div>
                                    <button class="btn btn-primary">Valor Sob Consulta</button>
                                </div>
                            </div>
                        </div>

                        <div class="col mt-3 mt-lg-0" data-aos="zoom-in-left" data-aos-duration="1000">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="card-header">
                                        <h3>Enterprise</h3>
                                    </div>
                                    <div class="card-content">
                                        <p class="plan-type">🏦 Grandes organizações</p>
                                        <small class="subtitle">Inclui:</small>
                                        <ul class="benefits">
                                            <li>♾️ Diagnósticos ilimitados</li>
                                            <li>👥 +100 usuários</li>
                                            <li>📅 Calendário customizado</li>
                                            <li>🤝 Suporte dedicado</li>
                                        </ul>
                                    </div>
                                    <button class="btn btn-primary">Valor Sob Consulta</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')

@endpush
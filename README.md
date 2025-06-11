<h1 align="center">🚀 SaaS Endomarketing</h1>

<p align="center">
  Plataforma SaaS para diagnóstico organizacional, plano de ação automatizado e campanhas internas com base em calendário estratégico.
</p>

<h2>🔧 Funcionalidades</h2>

<ul>
  <li>📋 Diagnóstico personalizado para equipes e empresas</li>
  <li>📊 Relatórios com plano de ação automático</li>
  <li>🗓️ Campanhas automáticas com base em calendário</li>
  <li>👥 Controle de colaboradores por plano</li>
  <li>📈 Dashboard administrativo</li>
  <li>📬 Comunicação interna contínua</li>
</ul>

<h2>🛠️ Tecnologias</h2>

<ul>
  <li><strong>Back-end:</strong> Laravel 11, PHP 8.x</li>
  <li><strong>Front-end:</strong> Blade, Bootstrap 5, Sass</li>
  <li><strong>Painel Administrativo:</strong> Filament</li>
  <li><strong>Banco de dados:</strong> MySQL</li>
</ul>

<h2>📦 Instalação</h2>

<pre>
git clone https://github.com/mateus-dpaula10/saas-endomarketing.git
cd saas-endomarketing

# Instalar dependências
composer install
npm install && npm run dev

# Copiar arquivo de ambiente e gerar chave
cp .env.example .env
php artisan key:generate
</pre>

<h2>⚙️ Configuração Adicional</h2>

- Configure o banco de dados no arquivo `.env`
- Execute as migrations:

<pre>
php artisan migrate --seed
</pre>
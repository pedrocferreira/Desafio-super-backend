<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-50 shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
          <div class="flex items-center gap-3">
            <div class="bg-indigo-600 text-white p-2 rounded-lg">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
            </div>
            <h1 class="text-xl font-bold text-gray-900">Super Backend <span class="text-indigo-600">API</span></h1>
            <span class="px-2 py-1 text-xs font-semibold rounded bg-gray-100 text-gray-600">v1.0</span>
          </div>
          <div class="flex items-center gap-4">
            <a href="/test-api" class="px-4 py-2 text-sm font-medium text-indigo-600 hover:text-indigo-800 border border-indigo-200 rounded-md hover:bg-indigo-50 transition-colors">
              🧪 Testar API
            </a>
          </div>
        </div>
      </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <!-- Hero Section -->
      <div class="text-center mb-16">
        <h2 class="text-4xl font-bold text-gray-900 mb-4">Documentação da API</h2>
        <p class="text-xl text-gray-600 max-w-3xl mx-auto">
          API RESTful para integração com subadquirentes de pagamento (PIX e Saques).
          Desenvolvida com Laravel 10 e PHP 8.3.
        </p>
      </div>

      <!-- Table of Contents -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">📑 Índice</h3>
        <nav class="grid md:grid-cols-2 gap-2">
          <a href="#introducao" class="text-indigo-600 hover:text-indigo-800 hover:underline">Introdução</a>
          <a href="#autenticacao" class="text-indigo-600 hover:text-indigo-800 hover:underline">Autenticação</a>
          <a href="#endpoints-pix" class="text-indigo-600 hover:text-indigo-800 hover:underline">Endpoints PIX</a>
          <a href="#endpoints-saques" class="text-indigo-600 hover:text-indigo-800 hover:underline">Endpoints Saques</a>
          <a href="#endpoints-pagamentos" class="text-indigo-600 hover:text-indigo-800 hover:underline">Endpoints Pagamentos</a>
          <a href="#respostas" class="text-indigo-600 hover:text-indigo-800 hover:underline">Estrutura de Respostas</a>
          <a href="#erros" class="text-indigo-600 hover:text-indigo-800 hover:underline">Códigos de Erro</a>
        </nav>
      </div>

      <!-- Introduction -->
      <section id="introducao" class="mb-12">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
          <h2 class="text-2xl font-bold text-gray-900 mb-4">📖 Introdução</h2>
          <div class="prose max-w-none">
            <p class="text-gray-700 mb-4">
              A Super Backend API é uma solução completa para processamento de pagamentos via PIX e saques bancários,
              com suporte a múltiplas subadquirentes através do padrão Strategy Pattern.
            </p>
            <h3 class="text-lg font-semibold text-gray-900 mt-6 mb-3">Base URL</h3>
            <div class="bg-gray-900 rounded-lg p-4 text-gray-300 font-mono text-sm">
              {{ baseUrl }}/api/v1
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mt-6 mb-3">Formato de Dados</h3>
            <p class="text-gray-700 mb-2">
              Todas as requisições devem enviar dados no formato <code class="bg-gray-100 px-2 py-1 rounded text-sm">JSON</code>
              com o header <code class="bg-gray-100 px-2 py-1 rounded text-sm">Content-Type: application/json</code>.
            </p>
            <p class="text-gray-700">
              Todas as respostas são retornadas no formato <code class="bg-gray-100 px-2 py-1 rounded text-sm">JSON</code>.
            </p>
          </div>
        </div>
      </section>

      <!-- Authentication -->
      <section id="autenticacao" class="mb-12">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
          <h2 class="text-2xl font-bold text-gray-900 mb-6">🔐 Autenticação</h2>
          
          <p class="text-gray-700 mb-6">
            A API utiliza <strong>Laravel Sanctum</strong> para autenticação via tokens. A maioria dos endpoints requer
            autenticação através do header <code class="bg-gray-100 px-2 py-1 rounded text-sm">Authorization: Bearer {token}</code>.
          </p>

          <div class="space-y-8">
            <!-- Register -->
            <div>
              <h3 class="text-xl font-semibold text-gray-900 mb-3">
                <span class="inline-block px-2 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded mr-2">POST</span>
                Registrar Usuário
              </h3>
              <div class="bg-gray-900 rounded-lg p-4 text-gray-300 font-mono text-sm mb-3">
                POST /api/v1/auth/register
              </div>
              <p class="text-gray-700 mb-4">Cria um novo usuário no sistema e retorna um token de autenticação.</p>
              
              <h4 class="text-md font-semibold text-gray-900 mb-2">Parâmetros</h4>
              <div class="overflow-x-auto mb-4">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                  <thead class="bg-gray-50">
                    <tr>
                      <th class="px-4 py-2 text-left text-gray-700 font-semibold">Campo</th>
                      <th class="px-4 py-2 text-left text-gray-700 font-semibold">Tipo</th>
                      <th class="px-4 py-2 text-left text-gray-700 font-semibold">Obrigatório</th>
                      <th class="px-4 py-2 text-left text-gray-700 font-semibold">Descrição</th>
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y divide-gray-200">
                    <tr>
                      <td class="px-4 py-2 font-mono text-gray-900">name</td>
                      <td class="px-4 py-2 text-gray-700">string</td>
                      <td class="px-4 py-2 text-gray-700">Sim</td>
                      <td class="px-4 py-2 text-gray-700">Nome completo do usuário</td>
                    </tr>
                    <tr>
                      <td class="px-4 py-2 font-mono text-gray-900">email</td>
                      <td class="px-4 py-2 text-gray-700">string</td>
                      <td class="px-4 py-2 text-gray-700">Sim</td>
                      <td class="px-4 py-2 text-gray-700">Email único do usuário</td>
                    </tr>
                    <tr>
                      <td class="px-4 py-2 font-mono text-gray-900">password</td>
                      <td class="px-4 py-2 text-gray-700">string</td>
                      <td class="px-4 py-2 text-gray-700">Sim</td>
                      <td class="px-4 py-2 text-gray-700">Senha (mínimo 8 caracteres)</td>
                    </tr>
                    <tr>
                      <td class="px-4 py-2 font-mono text-gray-900">password_confirmation</td>
                      <td class="px-4 py-2 text-gray-700">string</td>
                      <td class="px-4 py-2 text-gray-700">Sim</td>
                      <td class="px-4 py-2 text-gray-700">Confirmação da senha</td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <h4 class="text-md font-semibold text-gray-900 mb-2">Exemplo de Requisição</h4>
              <div class="bg-gray-900 rounded-lg p-4 text-gray-300 font-mono text-xs overflow-x-auto mb-4">
<pre>curl -X POST {{ baseUrl }}/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "João Silva",
    "email": "joao@example.com",
    "password": "senha123",
    "password_confirmation": "senha123"
  }'</pre>
              </div>

              <h4 class="text-md font-semibold text-gray-900 mb-2">Exemplo de Resposta (201)</h4>
              <div class="bg-gray-900 rounded-lg p-4 text-gray-300 font-mono text-xs overflow-x-auto">
<pre>{
  "message": "Usuário registrado com sucesso.",
  "token": "1|abc123def456...",
  "user": {
    "id": 1,
    "name": "João Silva",
    "email": "joao@example.com"
  }
}</pre>
              </div>
            </div>

            <!-- Login -->
            <div>
              <h3 class="text-xl font-semibold text-gray-900 mb-3">
                <span class="inline-block px-2 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded mr-2">POST</span>
                Login
              </h3>
              <div class="bg-gray-900 rounded-lg p-4 text-gray-300 font-mono text-sm mb-3">
                POST /api/v1/auth/login
              </div>
              <p class="text-gray-700 mb-4">Autentica um usuário e retorna um token de acesso.</p>
              
              <h4 class="text-md font-semibold text-gray-900 mb-2">Parâmetros</h4>
              <div class="overflow-x-auto mb-4">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                  <thead class="bg-gray-50">
                    <tr>
                      <th class="px-4 py-2 text-left text-gray-700 font-semibold">Campo</th>
                      <th class="px-4 py-2 text-left text-gray-700 font-semibold">Tipo</th>
                      <th class="px-4 py-2 text-left text-gray-700 font-semibold">Obrigatório</th>
                      <th class="px-4 py-2 text-left text-gray-700 font-semibold">Descrição</th>
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y divide-gray-200">
                    <tr>
                      <td class="px-4 py-2 font-mono text-gray-900">email</td>
                      <td class="px-4 py-2 text-gray-700">string</td>
                      <td class="px-4 py-2 text-gray-700">Sim</td>
                      <td class="px-4 py-2 text-gray-700">Email do usuário</td>
                    </tr>
                    <tr>
                      <td class="px-4 py-2 font-mono text-gray-900">password</td>
                      <td class="px-4 py-2 text-gray-700">string</td>
                      <td class="px-4 py-2 text-gray-700">Sim</td>
                      <td class="px-4 py-2 text-gray-700">Senha do usuário</td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <h4 class="text-md font-semibold text-gray-900 mb-2">Exemplo de Requisição</h4>
              <div class="bg-gray-900 rounded-lg p-4 text-gray-300 font-mono text-xs overflow-x-auto mb-4">
<pre>curl -X POST {{ baseUrl }}/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@admin.com",
    "password": "admin1234"
  }'</pre>
              </div>

              <h4 class="text-md font-semibold text-gray-900 mb-2">Exemplo de Resposta (200)</h4>
              <div class="bg-gray-900 rounded-lg p-4 text-gray-300 font-mono text-xs overflow-x-auto">
<pre>{
  "message": "Login realizado com sucesso.",
  "token": "2|xyz789...",
  "user": {
    "id": 1,
    "name": "Administrador",
    "email": "admin@admin.com"
  }
}</pre>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- PIX Endpoints -->
      <section id="endpoints-pix" class="mb-12">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
          <h2 class="text-2xl font-bold text-gray-900 mb-6">💠 Endpoints PIX</h2>
          
          <div class="space-y-8">
            <!-- Create PIX -->
            <div>
              <h3 class="text-xl font-semibold text-gray-900 mb-3">
                <span class="inline-block px-2 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded mr-2">POST</span>
                Gerar PIX
              </h3>
              <div class="bg-gray-900 rounded-lg p-4 text-gray-300 font-mono text-sm mb-3">
                POST /api/v1/pix
              </div>
              <p class="text-gray-700 mb-4">Cria uma cobrança PIX instantânea.</p>
              
              <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                <p class="text-sm text-yellow-800">
                  <strong>⚠️ Requer autenticação:</strong> Envie o header <code class="bg-yellow-100 px-1 py-0.5 rounded">Authorization: Bearer {token}</code>
                </p>
              </div>

              <h4 class="text-md font-semibold text-gray-900 mb-2">Parâmetros</h4>
              <div class="overflow-x-auto mb-4">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                  <thead class="bg-gray-50">
                    <tr>
                      <th class="px-4 py-2 text-left text-gray-700 font-semibold">Campo</th>
                      <th class="px-4 py-2 text-left text-gray-700 font-semibold">Tipo</th>
                      <th class="px-4 py-2 text-left text-gray-700 font-semibold">Obrigatório</th>
                      <th class="px-4 py-2 text-left text-gray-700 font-semibold">Descrição</th>
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y divide-gray-200">
                    <tr>
                      <td class="px-4 py-2 font-mono text-gray-900">amount</td>
                      <td class="px-4 py-2 text-gray-700">float</td>
                      <td class="px-4 py-2 text-gray-700">Sim</td>
                      <td class="px-4 py-2 text-gray-700">Valor do PIX (0.01 a 999999.99)</td>
                    </tr>
                    <tr>
                      <td class="px-4 py-2 font-mono text-gray-900">payer_name</td>
                      <td class="px-4 py-2 text-gray-700">string</td>
                      <td class="px-4 py-2 text-gray-700">Não</td>
                      <td class="px-4 py-2 text-gray-700">Nome do pagador</td>
                    </tr>
                    <tr>
                      <td class="px-4 py-2 font-mono text-gray-900">payer_document</td>
                      <td class="px-4 py-2 text-gray-700">string</td>
                      <td class="px-4 py-2 text-gray-700">Não</td>
                      <td class="px-4 py-2 text-gray-700">CPF ou CNPJ do pagador</td>
                    </tr>
                    <tr>
                      <td class="px-4 py-2 font-mono text-gray-900">description</td>
                      <td class="px-4 py-2 text-gray-700">string</td>
                      <td class="px-4 py-2 text-gray-700">Não</td>
                      <td class="px-4 py-2 text-gray-700">Descrição do pagamento</td>
                    </tr>
                    <tr>
                      <td class="px-4 py-2 font-mono text-gray-900">subadquirente</td>
                      <td class="px-4 py-2 text-gray-700">string</td>
                      <td class="px-4 py-2 text-gray-700">Não</td>
                      <td class="px-4 py-2 text-gray-700">subadq_a ou subadq_b</td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <h4 class="text-md font-semibold text-gray-900 mb-2">Exemplo de Requisição</h4>
              <div class="bg-gray-900 rounded-lg p-4 text-gray-300 font-mono text-xs overflow-x-auto mb-4">
<pre>curl -X POST {{ baseUrl }}/api/v1/pix \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "amount": 150.50,
    "payer_name": "João Silva",
    "payer_document": "11144477735",
    "description": "Pedido #123"
  }'</pre>
              </div>

              <h4 class="text-md font-semibold text-gray-900 mb-2">Exemplo de Resposta (201)</h4>
              <div class="bg-gray-900 rounded-lg p-4 text-gray-300 font-mono text-xs overflow-x-auto">
<pre>{
  "message": "PIX gerado com sucesso.",
  "data": {
    "id": 1,
    "pix_id": "SP_SUBADQA_...",
    "qr_code": "00020126...",
    "qr_code_base64": "MDAwMjAxMjY...",
    "status": "pending",
    "amount": "150.50"
  }
}</pre>
              </div>
            </div>

            <!-- List PIX -->
            <div>
              <h3 class="text-xl font-semibold text-gray-900 mb-3">
                <span class="inline-block px-2 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded mr-2">GET</span>
                Listar PIX
              </h3>
              <div class="bg-gray-900 rounded-lg p-4 text-gray-300 font-mono text-sm mb-3">
                GET /api/v1/pix
              </div>
              <p class="text-gray-700 mb-4">Lista todos os PIX gerados pelo usuário autenticado.</p>
              
              <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                <p class="text-sm text-yellow-800">
                  <strong>⚠️ Requer autenticação:</strong> Envie o header <code class="bg-yellow-100 px-1 py-0.5 rounded">Authorization: Bearer {token}</code>
                </p>
              </div>
            </div>

            <!-- Get PIX Detail -->
            <div>
              <h3 class="text-xl font-semibold text-gray-900 mb-3">
                <span class="inline-block px-2 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded mr-2">GET</span>
                Detalhes do PIX
              </h3>
              <div class="bg-gray-900 rounded-lg p-4 text-gray-300 font-mono text-sm mb-3">
                GET /api/v1/pix/{id}
              </div>
              <p class="text-gray-700 mb-4">Retorna os detalhes de um PIX específico.</p>
              
              <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                <p class="text-sm text-yellow-800">
                  <strong>⚠️ Requer autenticação:</strong> Envie o header <code class="bg-yellow-100 px-1 py-0.5 rounded">Authorization: Bearer {token}</code>
                </p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Withdraws Endpoints -->
      <section id="endpoints-saques" class="mb-12">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
          <h2 class="text-2xl font-bold text-gray-900 mb-6">💸 Endpoints Saques</h2>
          
          <div class="space-y-8">
            <!-- Create Withdraw -->
            <div>
              <h3 class="text-xl font-semibold text-gray-900 mb-3">
                <span class="inline-block px-2 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded mr-2">POST</span>
                Solicitar Saque
              </h3>
              <div class="bg-gray-900 rounded-lg p-4 text-gray-300 font-mono text-sm mb-3">
                POST /api/v1/withdraws
              </div>
              <p class="text-gray-700 mb-4">Solicita um saque para uma conta bancária.</p>
              
              <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                <p class="text-sm text-yellow-800">
                  <strong>⚠️ Requer autenticação:</strong> Envie o header <code class="bg-yellow-100 px-1 py-0.5 rounded">Authorization: Bearer {token}</code>
                </p>
              </div>

              <h4 class="text-md font-semibold text-gray-900 mb-2">Parâmetros</h4>
              <div class="overflow-x-auto mb-4">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                  <thead class="bg-gray-50">
                    <tr>
                      <th class="px-4 py-2 text-left text-gray-700 font-semibold">Campo</th>
                      <th class="px-4 py-2 text-left text-gray-700 font-semibold">Tipo</th>
                      <th class="px-4 py-2 text-left text-gray-700 font-semibold">Obrigatório</th>
                      <th class="px-4 py-2 text-left text-gray-700 font-semibold">Descrição</th>
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y divide-gray-200">
                    <tr>
                      <td class="px-4 py-2 font-mono text-gray-900">amount</td>
                      <td class="px-4 py-2 text-gray-700">float</td>
                      <td class="px-4 py-2 text-gray-700">Sim</td>
                      <td class="px-4 py-2 text-gray-700">Valor do saque (mínimo 1.00)</td>
                    </tr>
                    <tr>
                      <td class="px-4 py-2 font-mono text-gray-900">bank_account</td>
                      <td class="px-4 py-2 text-gray-700">object</td>
                      <td class="px-4 py-2 text-gray-700">Sim</td>
                      <td class="px-4 py-2 text-gray-700">Dados da conta bancária</td>
                    </tr>
                    <tr>
                      <td class="px-4 py-2 font-mono text-gray-900">bank_account.bank</td>
                      <td class="px-4 py-2 text-gray-700">string</td>
                      <td class="px-4 py-2 text-gray-700">Sim</td>
                      <td class="px-4 py-2 text-gray-700">Nome do banco</td>
                    </tr>
                    <tr>
                      <td class="px-4 py-2 font-mono text-gray-900">bank_account.agency</td>
                      <td class="px-4 py-2 text-gray-700">string</td>
                      <td class="px-4 py-2 text-gray-700">Sim</td>
                      <td class="px-4 py-2 text-gray-700">Agência</td>
                    </tr>
                    <tr>
                      <td class="px-4 py-2 font-mono text-gray-900">bank_account.account</td>
                      <td class="px-4 py-2 text-gray-700">string</td>
                      <td class="px-4 py-2 text-gray-700">Sim</td>
                      <td class="px-4 py-2 text-gray-700">Número da conta</td>
                    </tr>
                    <tr>
                      <td class="px-4 py-2 font-mono text-gray-900">bank_account.account_type</td>
                      <td class="px-4 py-2 text-gray-700">string</td>
                      <td class="px-4 py-2 text-gray-700">Não</td>
                      <td class="px-4 py-2 text-gray-700">checking ou savings</td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <h4 class="text-md font-semibold text-gray-900 mb-2">Exemplo de Requisição</h4>
              <div class="bg-gray-900 rounded-lg p-4 text-gray-300 font-mono text-xs overflow-x-auto mb-4">
<pre>curl -X POST {{ baseUrl }}/api/v1/withdraws \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "amount": 200.00,
    "bank_account": {
      "bank": "Nubank",
      "agency": "0001",
      "account": "1234567-8",
      "account_type": "checking"
    }
  }'</pre>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Payments Endpoints -->
      <section id="endpoints-pagamentos" class="mb-12">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
          <h2 class="text-2xl font-bold text-gray-900 mb-6">💳 Endpoints Pagamentos</h2>
          
          <div>
            <h3 class="text-xl font-semibold text-gray-900 mb-3">
              <span class="inline-block px-2 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded mr-2">POST</span>
              Processar Pagamento
            </h3>
            <div class="bg-gray-900 rounded-lg p-4 text-gray-300 font-mono text-sm mb-3">
              POST /api/v1/payment/process
            </div>
            <p class="text-gray-700 mb-4">Processa um pagamento usando um gateway (Strategy Pattern).</p>
            
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
              <p class="text-sm text-yellow-800">
                <strong>⚠️ Requer autenticação:</strong> Envie o header <code class="bg-yellow-100 px-1 py-0.5 rounded">Authorization: Bearer {token}</code>
              </p>
            </div>

            <h4 class="text-md font-semibold text-gray-900 mb-2">Parâmetros</h4>
            <div class="overflow-x-auto mb-4">
              <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-4 py-2 text-left text-gray-700 font-semibold">Campo</th>
                    <th class="px-4 py-2 text-left text-gray-700 font-semibold">Tipo</th>
                    <th class="px-4 py-2 text-left text-gray-700 font-semibold">Obrigatório</th>
                    <th class="px-4 py-2 text-left text-gray-700 font-semibold">Descrição</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr>
                    <td class="px-4 py-2 font-mono text-gray-900">amount</td>
                    <td class="px-4 py-2 text-gray-700">float</td>
                    <td class="px-4 py-2 text-gray-700">Sim</td>
                    <td class="px-4 py-2 text-gray-700">Valor do pagamento</td>
                  </tr>
                  <tr>
                    <td class="px-4 py-2 font-mono text-gray-900">gateway_name</td>
                    <td class="px-4 py-2 text-gray-700">string</td>
                    <td class="px-4 py-2 text-gray-700">Sim</td>
                    <td class="px-4 py-2 text-gray-700">subadquirente_a ou subadquirente_b</td>
                  </tr>
                  <tr>
                    <td class="px-4 py-2 font-mono text-gray-900">payment_token</td>
                    <td class="px-4 py-2 text-gray-700">string</td>
                    <td class="px-4 py-2 text-gray-700">Sim</td>
                    <td class="px-4 py-2 text-gray-700">Token de pagamento</td>
                  </tr>
                  <tr>
                    <td class="px-4 py-2 font-mono text-gray-900">payee_id</td>
                    <td class="px-4 py-2 text-gray-700">integer</td>
                    <td class="px-4 py-2 text-gray-700">Sim</td>
                    <td class="px-4 py-2 text-gray-700">ID do usuário recebedor</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </section>

      <!-- Response Structure -->
      <section id="respostas" class="mb-12">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
          <h2 class="text-2xl font-bold text-gray-900 mb-6">📋 Estrutura de Respostas</h2>
          
          <h3 class="text-xl font-semibold text-gray-900 mb-4">Resposta de Sucesso</h3>
          <div class="bg-gray-900 rounded-lg p-4 text-gray-300 font-mono text-xs overflow-x-auto mb-6">
<pre>{
  "message": "Operação realizada com sucesso.",
  "data": {
    // Dados específicos da operação
  }
}</pre>
          </div>

          <h3 class="text-xl font-semibold text-gray-900 mb-4">Resposta de Erro</h3>
          <div class="bg-gray-900 rounded-lg p-4 text-gray-300 font-mono text-xs overflow-x-auto">
<pre>{
  "message": "Mensagem de erro",
  "errors": {
    "campo": ["Mensagem de validação"]
  }
}</pre>
          </div>
        </div>
      </section>

      <!-- Error Codes -->
      <section id="erros" class="mb-12">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
          <h2 class="text-2xl font-bold text-gray-900 mb-6">❌ Códigos de Erro</h2>
          
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-3 text-left text-gray-700 font-semibold">Código</th>
                  <th class="px-4 py-3 text-left text-gray-700 font-semibold">Descrição</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr>
                  <td class="px-4 py-3 font-mono text-gray-900">200</td>
                  <td class="px-4 py-3 text-gray-700">Sucesso</td>
                </tr>
                <tr>
                  <td class="px-4 py-3 font-mono text-gray-900">201</td>
                  <td class="px-4 py-3 text-gray-700">Criado com sucesso</td>
                </tr>
                <tr>
                  <td class="px-4 py-3 font-mono text-gray-900">401</td>
                  <td class="px-4 py-3 text-gray-700">Não autenticado</td>
                </tr>
                <tr>
                  <td class="px-4 py-3 font-mono text-gray-900">403</td>
                  <td class="px-4 py-3 text-gray-700">Não autorizado</td>
                </tr>
                <tr>
                  <td class="px-4 py-3 font-mono text-gray-900">404</td>
                  <td class="px-4 py-3 text-gray-700">Recurso não encontrado</td>
                </tr>
                <tr>
                  <td class="px-4 py-3 font-mono text-gray-900">422</td>
                  <td class="px-4 py-3 text-gray-700">Erro de validação</td>
                </tr>
                <tr>
                  <td class="px-4 py-3 font-mono text-gray-900">429</td>
                  <td class="px-4 py-3 text-gray-700">Muitas requisições (rate limit)</td>
                </tr>
                <tr>
                  <td class="px-4 py-3 font-mono text-gray-900">500</td>
                  <td class="px-4 py-3 text-gray-700">Erro interno do servidor</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-16">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="text-center text-gray-600 text-sm">
          <p>Super Backend API - Desenvolvido com Laravel 10 e PHP 8.3</p>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const baseUrl = ref('');

onMounted(() => {
  baseUrl.value = window.location.origin;
});
</script>

<template>
  <div class="min-h-screen bg-gray-50 font-sans text-gray-900">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="bg-indigo-600 text-white p-2 rounded-lg">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
          </div>
          <h1 class="text-xl font-bold text-gray-900">Super Backend <span class="text-indigo-600">API</span></h1>
        </div>
        
        <div class="flex items-center gap-4">
          <a href="/documentation" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 border border-gray-300 rounded-md hover:bg-gray-50 transition-colors">
            📚 Documentação
          </a>
          <div v-if="token" class="flex items-center gap-2 bg-green-50 text-green-700 px-3 py-1.5 rounded-full text-sm font-medium border border-green-200">
            <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
            Autenticado
            <button @click="logout" class="ml-2 text-green-800 hover:text-green-900 font-bold" title="Sair">×</button>
          </div>
          <div v-else class="flex items-center gap-2 bg-gray-100 text-gray-600 px-3 py-1.5 rounded-full text-sm font-medium border border-gray-200">
            <span class="w-2 h-2 bg-gray-400 rounded-full"></span>
            Não autenticado
          </div>
        </div>
      </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="grid grid-cols-12 gap-8">
        
        <!-- Sidebar Navigation -->
        <div class="col-span-12 md:col-span-3 lg:col-span-2">
          <nav class="space-y-1 sticky top-24">
            <a 
              v-for="section in sections" 
              :key="section.id"
              href="#"
              @click.prevent="activeSection = section.id"
              :class="[
                activeSection === section.id 
                  ? 'bg-indigo-50 text-indigo-700 border-l-4 border-indigo-600' 
                  : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 border-l-4 border-transparent',
                'group flex items-center px-3 py-2 text-sm font-medium rounded-r-md transition-colors duration-150'
              ]"
            >
              <span class="mr-3 text-lg">{{ section.icon }}</span>
              {{ section.name }}
            </a>
          </nav>
        </div>

        <!-- Content Area -->
        <div class="col-span-12 md:col-span-9 lg:col-span-10 space-y-8">
          
          <!-- Auth Section -->
          <div v-if="activeSection === 'auth'" class="space-y-6">
            <!-- Register -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
              <div class="p-6 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                <div>
                  <h2 class="text-lg font-semibold text-gray-900">📝 Registrar Usuário</h2>
                  <p class="text-sm text-gray-500 mt-1">Crie uma nova conta no sistema.</p>
                </div>
                <span class="px-2 py-1 text-xs font-semibold rounded bg-indigo-100 text-indigo-800">POST /api/v1/auth/register</span>
              </div>
              
              <div class="p-6">
                <div class="grid md:grid-cols-2 gap-8">
                  <div class="space-y-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1">Nome</label>
                      <input v-model="registerForm.name" type="text" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border" placeholder="João Silva">
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                      <input v-model="registerForm.email" type="email" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border" placeholder="joao@example.com">
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1">Senha</label>
                      <input v-model="registerForm.password" type="password" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border" placeholder="senha123">
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar Senha</label>
                      <input v-model="registerForm.password_confirmation" type="password" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border" placeholder="senha123">
                    </div>
                    <button 
                      @click="register" 
                      :disabled="loading"
                      class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    >
                      <span v-if="loading">Registrando...</span>
                      <span v-else>Registrar</span>
                    </button>
                  </div>
                  <div class="bg-gray-900 rounded-lg p-4 overflow-auto max-h-[400px] text-gray-300 font-mono text-xs relative group">
                    <div v-if="registerResponse" class="absolute top-2 right-2">
                      <span :class="registerResponse.status >= 200 && registerResponse.status < 300 ? 'text-green-400' : 'text-red-400'" class="font-bold">
                        {{ registerResponse.status }} {{ registerResponse.statusText }}
                      </span>
                    </div>
                    <pre v-if="registerResponse">{{ JSON.stringify(registerResponse.data, null, 2) }}</pre>
                    <div v-else class="h-full flex items-center justify-center text-gray-600 italic">
                      A resposta aparecerá aqui...
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Login -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
              <div class="p-6 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                <div>
                  <h2 class="text-lg font-semibold text-gray-900">🔐 Login</h2>
                  <p class="text-sm text-gray-500 mt-1">Autentique-se e obtenha um token de acesso.</p>
                </div>
                <span class="px-2 py-1 text-xs font-semibold rounded bg-indigo-100 text-indigo-800">POST /api/v1/auth/login</span>
              </div>
              
              <div class="p-6">
                <div class="grid md:grid-cols-2 gap-8">
                  <div class="space-y-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                      <input v-model="loginForm.email" type="email" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border" placeholder="admin@admin.com">
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1">Senha</label>
                      <input v-model="loginForm.password" type="password" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border" placeholder="admin1234">
                    </div>
                    <button 
                      @click="login" 
                      :disabled="loading"
                      class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    >
                      <span v-if="loading">Entrando...</span>
                      <span v-else>Entrar</span>
                    </button>
                    <div v-if="token" class="bg-green-50 border border-green-200 rounded-lg p-4">
                      <p class="text-sm text-green-800 font-medium mb-2">Token de acesso:</p>
                      <p class="text-xs text-green-700 font-mono break-all">{{ token }}</p>
                    </div>
                  </div>
                  <div class="bg-gray-900 rounded-lg p-4 overflow-auto max-h-[400px] text-gray-300 font-mono text-xs relative group">
                    <div v-if="loginResponse" class="absolute top-2 right-2">
                      <span :class="loginResponse.status >= 200 && loginResponse.status < 300 ? 'text-green-400' : 'text-red-400'" class="font-bold">
                        {{ loginResponse.status }} {{ loginResponse.statusText }}
                      </span>
                    </div>
                    <pre v-if="loginResponse">{{ JSON.stringify(loginResponse.data, null, 2) }}</pre>
                    <div v-else class="h-full flex items-center justify-center text-gray-600 italic">
                      A resposta aparecerá aqui...
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- User Info -->
            <div v-if="token" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
              <div class="p-6 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                <div>
                  <h2 class="text-lg font-semibold text-gray-900">👤 Dados do Usuário</h2>
                  <p class="text-sm text-gray-500 mt-1">Obtenha informações do usuário autenticado.</p>
                </div>
                <div class="flex gap-2">
                  <span class="px-2 py-1 text-xs font-semibold rounded bg-indigo-100 text-indigo-800">GET /api/v1/user</span>
                  <button 
                    @click="getUser" 
                    :disabled="loading"
                    class="px-3 py-1 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                  >
                    Buscar
                  </button>
                </div>
              </div>
              <div class="p-6">
                <div class="bg-gray-900 rounded-lg p-4 overflow-auto max-h-[400px] text-gray-300 font-mono text-xs relative group">
                  <div v-if="userResponse" class="absolute top-2 right-2">
                    <span :class="userResponse.status >= 200 && userResponse.status < 300 ? 'text-green-400' : 'text-red-400'" class="font-bold">
                      {{ userResponse.status }} {{ userResponse.statusText }}
                    </span>
                  </div>
                  <pre v-if="userResponse">{{ JSON.stringify(userResponse.data, null, 2) }}</pre>
                  <div v-else class="h-full flex items-center justify-center text-gray-600 italic">
                    A resposta aparecerá aqui...
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- PIX Section -->
          <div v-if="activeSection === 'pix'" class="space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
              <div class="p-6 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                <div>
                  <h2 class="text-lg font-semibold text-gray-900">💠 Gerar PIX</h2>
                  <p class="text-sm text-gray-500 mt-1">Crie uma cobrança PIX instantânea.</p>
                </div>
                <span class="px-2 py-1 text-xs font-semibold rounded bg-indigo-100 text-indigo-800">POST /api/v1/pix</span>
              </div>
              
              <div class="p-6">
                <div class="grid md:grid-cols-2 gap-8">
                  <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                      <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Valor (R$)</label>
                        <input v-model="pixForm.amount" type="number" step="0.01" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
                      </div>
                      <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">CPF Pagador</label>
                        <input v-model="pixForm.payer_document" type="text" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
                      </div>
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1">Nome Pagador</label>
                      <input v-model="pixForm.payer_name" type="text" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                      <input v-model="pixForm.description" type="text" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1">Subadquirente (opcional)</label>
                      <select v-model="pixForm.subadquirente" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
                        <option value="">Padrão</option>
                        <option value="subadq_a">Subadq A</option>
                        <option value="subadq_b">Subadq B</option>
                      </select>
                    </div>
                    
                    <div class="pt-2">
                      <button 
                        @click="createPix" 
                        :disabled="loading || !token"
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                      >
                        <span v-if="!token">Faça login primeiro</span>
                        <span v-else-if="loading">Processando...</span>
                        <span v-else>Gerar PIX</span>
                      </button>
                    </div>
                  </div>

                  <!-- Response Area -->
                  <div class="bg-gray-900 rounded-lg p-4 overflow-auto max-h-[400px] text-gray-300 font-mono text-xs relative group">
                    <div v-if="pixResponse" class="absolute top-2 right-2">
                      <span :class="pixResponse.status >= 200 && pixResponse.status < 300 ? 'text-green-400' : 'text-red-400'" class="font-bold">
                        {{ pixResponse.status }} {{ pixResponse.statusText }}
                      </span>
                    </div>
                    <pre v-if="pixResponse">{{ JSON.stringify(pixResponse.data, null, 2) }}</pre>
                    <div v-else class="h-full flex items-center justify-center text-gray-600 italic">
                      A resposta aparecerá aqui...
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- List PIX -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
              <div class="p-6 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                <div>
                  <h2 class="text-lg font-semibold text-gray-900">📋 Listar PIX</h2>
                  <p class="text-sm text-gray-500 mt-1">Liste todos os PIX gerados.</p>
                </div>
                <div class="flex gap-2">
                  <span class="px-2 py-1 text-xs font-semibold rounded bg-indigo-100 text-indigo-800">GET /api/v1/pix</span>
                  <button 
                    @click="listPix" 
                    :disabled="loading || !token"
                    class="px-3 py-1 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                  >
                    Atualizar
                  </button>
                </div>
              </div>
              
              <div class="p-6">
                <div v-if="pixList.length > 0" class="space-y-4">
                  <div v-for="pix in pixList" :key="pix.id" class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50">
                    <div class="flex justify-between items-start">
                      <div>
                        <div class="flex items-center gap-2">
                          <span class="font-semibold text-gray-900">PIX #{{ pix.id }}</span>
                          <span class="px-2 py-1 text-xs font-semibold rounded-full" :class="getStatusClass(pix.status)">
                            {{ pix.status }}
                          </span>
                        </div>
                        <p class="text-sm text-gray-600 mt-1">R$ {{ parseFloat(pix.amount).toFixed(2) }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ new Date(pix.created_at).toLocaleString() }}</p>
                      </div>
                      <button 
                        @click="showPixDetail(pix.id)"
                        class="px-3 py-1 text-xs font-medium text-indigo-600 hover:text-indigo-800 border border-indigo-200 rounded-md hover:bg-indigo-50"
                      >
                        Ver Detalhes
                      </button>
                    </div>
                  </div>
                </div>
                <div v-else class="text-center py-8 text-gray-500 italic">
                  Nenhum PIX encontrado.
                </div>
              </div>
            </div>

            <!-- PIX Detail -->
            <div v-if="selectedPixId" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
              <div class="p-6 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                <div>
                  <h2 class="text-lg font-semibold text-gray-900">🔍 Detalhes do PIX #{{ selectedPixId }}</h2>
                  <p class="text-sm text-gray-500 mt-1">Visualize informações detalhadas de um PIX específico.</p>
                </div>
                <div class="flex gap-2">
                  <span class="px-2 py-1 text-xs font-semibold rounded bg-indigo-100 text-indigo-800">GET /api/v1/pix/{{ selectedPixId }}</span>
                  <button 
                    @click="selectedPixId = null"
                    class="px-3 py-1 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-50"
                  >
                    Fechar
                  </button>
                </div>
              </div>
              <div class="p-6">
                <div class="bg-gray-900 rounded-lg p-4 overflow-auto max-h-[400px] text-gray-300 font-mono text-xs relative group">
                  <div v-if="pixDetailResponse" class="absolute top-2 right-2">
                    <span :class="pixDetailResponse.status >= 200 && pixDetailResponse.status < 300 ? 'text-green-400' : 'text-red-400'" class="font-bold">
                      {{ pixDetailResponse.status }} {{ pixDetailResponse.statusText }}
                    </span>
                  </div>
                  <pre v-if="pixDetailResponse">{{ JSON.stringify(pixDetailResponse.data, null, 2) }}</pre>
                  <div v-else class="h-full flex items-center justify-center text-gray-600 italic">
                    A resposta aparecerá aqui...
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Withdraws Section -->
          <div v-if="activeSection === 'withdraw'" class="space-y-6">
            <!-- Create Withdraw -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
              <div class="p-6 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                <div>
                  <h2 class="text-lg font-semibold text-gray-900">💸 Solicitar Saque</h2>
                  <p class="text-sm text-gray-500 mt-1">Transfira saldo para uma conta bancária.</p>
                </div>
                <span class="px-2 py-1 text-xs font-semibold rounded bg-indigo-100 text-indigo-800">POST /api/v1/withdraws</span>
              </div>
              
              <div class="p-6">
                <div class="grid md:grid-cols-2 gap-8">
                  <div class="space-y-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1">Valor (R$)</label>
                      <input v-model="withdrawForm.amount" type="number" step="0.01" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                      <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Banco</label>
                        <input v-model="withdrawForm.bank_account.bank" type="text" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border" placeholder="Nubank">
                      </div>
                      <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipo Conta</label>
                        <select v-model="withdrawForm.bank_account.account_type" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
                          <option value="checking">Corrente</option>
                          <option value="savings">Poupança</option>
                        </select>
                      </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                      <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Agência</label>
                        <input v-model="withdrawForm.bank_account.agency" type="text" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border" placeholder="0001">
                      </div>
                      <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Conta</label>
                        <input v-model="withdrawForm.bank_account.account" type="text" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border" placeholder="1234567-8">
                      </div>
                    </div>
                    <button 
                      @click="createWithdraw" 
                      :disabled="loading || !token"
                      class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    >
                      <span v-if="!token">Faça login primeiro</span>
                      <span v-else-if="loading">Processando...</span>
                      <span v-else>Solicitar Saque</span>
                    </button>
                  </div>
                  <div class="bg-gray-900 rounded-lg p-4 overflow-auto max-h-[400px] text-gray-300 font-mono text-xs relative group">
                    <div v-if="withdrawResponse" class="absolute top-2 right-2">
                      <span :class="withdrawResponse.status >= 200 && withdrawResponse.status < 300 ? 'text-green-400' : 'text-red-400'" class="font-bold">
                        {{ withdrawResponse.status }} {{ withdrawResponse.statusText }}
                      </span>
                    </div>
                    <pre v-if="withdrawResponse">{{ JSON.stringify(withdrawResponse.data, null, 2) }}</pre>
                    <div v-else class="h-full flex items-center justify-center text-gray-600 italic">
                      A resposta aparecerá aqui...
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- List Withdraws -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
              <div class="p-6 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                <div>
                  <h2 class="text-lg font-semibold text-gray-900">📋 Listar Saques</h2>
                  <p class="text-sm text-gray-500 mt-1">Liste todos os saques solicitados.</p>
                </div>
                <div class="flex gap-2">
                  <span class="px-2 py-1 text-xs font-semibold rounded bg-indigo-100 text-indigo-800">GET /api/v1/withdraws</span>
                  <button 
                    @click="listWithdraws" 
                    :disabled="loading || !token"
                    class="px-3 py-1 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                  >
                    Atualizar
                  </button>
                </div>
              </div>
              
              <div class="p-6">
                <div v-if="withdrawList.length > 0" class="space-y-4">
                  <div v-for="withdraw in withdrawList" :key="withdraw.id" class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50">
                    <div class="flex justify-between items-start">
                      <div>
                        <div class="flex items-center gap-2">
                          <span class="font-semibold text-gray-900">Saque #{{ withdraw.id }}</span>
                          <span class="px-2 py-1 text-xs font-semibold rounded-full" :class="getStatusClass(withdraw.status)">
                            {{ withdraw.status }}
                          </span>
                        </div>
                        <p class="text-sm text-gray-600 mt-1">R$ {{ parseFloat(withdraw.amount).toFixed(2) }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ new Date(withdraw.created_at).toLocaleString() }}</p>
                      </div>
                      <button 
                        @click="showWithdrawDetail(withdraw.id)"
                        class="px-3 py-1 text-xs font-medium text-indigo-600 hover:text-indigo-800 border border-indigo-200 rounded-md hover:bg-indigo-50"
                      >
                        Ver Detalhes
                      </button>
                    </div>
                  </div>
                </div>
                <div v-else class="text-center py-8 text-gray-500 italic">
                  Nenhum saque encontrado.
                </div>
              </div>
            </div>

            <!-- Withdraw Detail -->
            <div v-if="selectedWithdrawId" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
              <div class="p-6 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                <div>
                  <h2 class="text-lg font-semibold text-gray-900">🔍 Detalhes do Saque #{{ selectedWithdrawId }}</h2>
                  <p class="text-sm text-gray-500 mt-1">Visualize informações detalhadas de um saque específico.</p>
                </div>
                <div class="flex gap-2">
                  <span class="px-2 py-1 text-xs font-semibold rounded bg-indigo-100 text-indigo-800">GET /api/v1/withdraws/{{ selectedWithdrawId }}</span>
                  <button 
                    @click="selectedWithdrawId = null"
                    class="px-3 py-1 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-50"
                  >
                    Fechar
                  </button>
                </div>
              </div>
              <div class="p-6">
                <div class="bg-gray-900 rounded-lg p-4 overflow-auto max-h-[400px] text-gray-300 font-mono text-xs relative group">
                  <div v-if="withdrawDetailResponse" class="absolute top-2 right-2">
                    <span :class="withdrawDetailResponse.status >= 200 && withdrawDetailResponse.status < 300 ? 'text-green-400' : 'text-red-400'" class="font-bold">
                      {{ withdrawDetailResponse.status }} {{ withdrawDetailResponse.statusText }}
                    </span>
                  </div>
                  <pre v-if="withdrawDetailResponse">{{ JSON.stringify(withdrawDetailResponse.data, null, 2) }}</pre>
                  <div v-else class="h-full flex items-center justify-center text-gray-600 italic">
                    A resposta aparecerá aqui...
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Payments Section -->
          <div v-if="activeSection === 'payments'" class="space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
              <div class="p-6 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                <div>
                  <h2 class="text-lg font-semibold text-gray-900">💳 Processar Pagamento</h2>
                  <p class="text-sm text-gray-500 mt-1">Processe um pagamento usando um gateway.</p>
                </div>
                <span class="px-2 py-1 text-xs font-semibold rounded bg-indigo-100 text-indigo-800">POST /api/v1/payment/process</span>
              </div>
              
              <div class="p-6">
                <div class="grid md:grid-cols-2 gap-8">
                  <div class="space-y-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1">Valor (R$)</label>
                      <input v-model="paymentForm.amount" type="number" step="0.01" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1">Gateway</label>
                      <select v-model="paymentForm.gateway_name" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
                        <option value="subadquirente_a">Subadquirente A (Sucesso)</option>
                        <option value="subadquirente_b">Subadquirente B (Falha)</option>
                      </select>
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1">Token de Pagamento</label>
                      <input v-model="paymentForm.payment_token" type="text" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border" placeholder="tok_123456789">
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1">ID do Recebedor</label>
                      <input v-model="paymentForm.payee_id" type="number" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border" placeholder="2">
                    </div>
                    <button 
                      @click="processPayment" 
                      :disabled="loading || !token"
                      class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    >
                      <span v-if="!token">Faça login primeiro</span>
                      <span v-else-if="loading">Processando...</span>
                      <span v-else>Processar Pagamento</span>
                    </button>
                  </div>
                  <div class="bg-gray-900 rounded-lg p-4 overflow-auto max-h-[400px] text-gray-300 font-mono text-xs relative group">
                    <div v-if="paymentResponse" class="absolute top-2 right-2">
                      <span :class="paymentResponse.status >= 200 && paymentResponse.status < 300 ? 'text-green-400' : 'text-red-400'" class="font-bold">
                        {{ paymentResponse.status }} {{ paymentResponse.statusText }}
                      </span>
                    </div>
                    <pre v-if="paymentResponse">{{ JSON.stringify(paymentResponse.data, null, 2) }}</pre>
                    <div v-else class="h-full flex items-center justify-center text-gray-600 italic">
                      A resposta aparecerá aqui...
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import axios from 'axios';

// State
const token = ref(localStorage.getItem('api_token') || '');
const activeSection = ref('auth');
const loading = ref(false);

// Lists
const pixList = ref([]);
const withdrawList = ref([]);
const selectedPixId = ref(null);
const selectedWithdrawId = ref(null);

// Forms
const registerForm = reactive({
  name: 'João Silva',
  email: 'joao@example.com',
  password: 'senha123',
  password_confirmation: 'senha123'
});

const loginForm = reactive({
  email: 'admin@admin.com',
  password: 'admin1234'
});

const pixForm = reactive({
  amount: 150.50,
  payer_name: 'Cliente Teste',
  payer_document: '11144477735',
  description: 'Pedido #123',
  subadquirente: ''
});

const withdrawForm = reactive({
  amount: 200.00,
  bank_account: {
    bank: 'Nubank',
    agency: '0001',
    account: '1234567-8',
    account_type: 'checking'
  }
});

const paymentForm = reactive({
  amount: 100.50,
  gateway_name: 'subadquirente_a',
  payment_token: 'tok_123456789',
  payee_id: 2
});

// Responses
const registerResponse = ref(null);
const loginResponse = ref(null);
const userResponse = ref(null);
const pixResponse = ref(null);
const pixDetailResponse = ref(null);
const withdrawResponse = ref(null);
const withdrawDetailResponse = ref(null);
const paymentResponse = ref(null);

// Navigation
const sections = [
  { id: 'auth', name: 'Autenticação', icon: '🔐' },
  { id: 'pix', name: 'PIX', icon: '💠' },
  { id: 'withdraw', name: 'Saques', icon: '💸' },
  { id: 'payments', name: 'Pagamentos', icon: '💳' },
];

// Helper functions
const getStatusClass = (status) => {
  const statusMap = {
    'pending': 'bg-yellow-100 text-yellow-800',
    'completed': 'bg-green-100 text-green-800',
    'failed': 'bg-red-100 text-red-800',
    'processing': 'bg-blue-100 text-blue-800',
  };
  return statusMap[status] || 'bg-gray-100 text-gray-800';
};

const getAuthHeaders = () => {
  return token.value ? { Authorization: `Bearer ${token.value}` } : {};
};

// Auth actions
const register = async () => {
  loading.value = true;
  registerResponse.value = null;
  try {
    const response = await axios.post('/api/v1/auth/register', registerForm);
    registerResponse.value = {
      status: response.status,
      statusText: response.statusText,
      data: response.data
    };
    if (response.data.token) {
      token.value = response.data.token;
      localStorage.setItem('api_token', token.value);
    }
  } catch (error) {
    registerResponse.value = {
      status: error.response?.status || 500,
      statusText: error.response?.statusText || 'Error',
      data: error.response?.data || error.message
    };
  } finally {
    loading.value = false;
  }
};

const login = async () => {
  loading.value = true;
  loginResponse.value = null;
  try {
    const response = await axios.post('/api/v1/auth/login', loginForm);
    token.value = response.data.token;
    localStorage.setItem('api_token', token.value);
    loginResponse.value = {
      status: response.status,
      statusText: response.statusText,
      data: response.data
    };
    activeSection.value = 'pix';
  } catch (error) {
    loginResponse.value = {
      status: error.response?.status || 500,
      statusText: error.response?.statusText || 'Error',
      data: error.response?.data || error.message
    };
  } finally {
    loading.value = false;
  }
};

const logout = async () => {
  if (!confirm('Tem certeza que deseja sair?')) return;
  
  try {
    await axios.post('/api/v1/auth/logout', {}, {
      headers: getAuthHeaders()
    });
  } catch (e) {
    console.error(e);
  } finally {
    token.value = '';
    localStorage.removeItem('api_token');
    activeSection.value = 'auth';
  }
};

const getUser = async () => {
  if (!token.value) return;
  loading.value = true;
  userResponse.value = null;
  try {
    const response = await axios.get('/api/v1/user', {
      headers: getAuthHeaders()
    });
    userResponse.value = {
      status: response.status,
      statusText: response.statusText,
      data: response.data
    };
  } catch (error) {
    userResponse.value = {
      status: error.response?.status || 500,
      statusText: error.response?.statusText || 'Error',
      data: error.response?.data || error.message
    };
  } finally {
    loading.value = false;
  }
};

// PIX actions
const createPix = async () => {
  loading.value = true;
  pixResponse.value = null;
  try {
    const formData = { ...pixForm };
    if (!formData.subadquirente) delete formData.subadquirente;
    
    const response = await axios.post('/api/v1/pix', formData, {
      headers: getAuthHeaders()
    });
    pixResponse.value = {
      status: response.status,
      statusText: response.statusText,
      data: response.data
    };
    listPix();
  } catch (error) {
    pixResponse.value = {
      status: error.response?.status || 500,
      statusText: error.response?.statusText || 'Error',
      data: error.response?.data || error.message
    };
  } finally {
    loading.value = false;
  }
};

const listPix = async () => {
  if (!token.value) return;
  loading.value = true;
  try {
    const response = await axios.get('/api/v1/pix', {
      headers: getAuthHeaders()
    });
    pixList.value = response.data.data || [];
  } catch (error) {
    console.error('Erro ao listar PIX:', error);
    pixList.value = [];
  } finally {
    loading.value = false;
  }
};

const showPixDetail = async (id) => {
  selectedPixId.value = id;
  loading.value = true;
  pixDetailResponse.value = null;
  try {
    const response = await axios.get(`/api/v1/pix/${id}`, {
      headers: getAuthHeaders()
    });
    pixDetailResponse.value = {
      status: response.status,
      statusText: response.statusText,
      data: response.data
    };
  } catch (error) {
    pixDetailResponse.value = {
      status: error.response?.status || 500,
      statusText: error.response?.statusText || 'Error',
      data: error.response?.data || error.message
    };
  } finally {
    loading.value = false;
  }
};

// Withdraw actions
const createWithdraw = async () => {
  loading.value = true;
  withdrawResponse.value = null;
  try {
    const response = await axios.post('/api/v1/withdraws', withdrawForm, {
      headers: getAuthHeaders()
    });
    withdrawResponse.value = {
      status: response.status,
      statusText: response.statusText,
      data: response.data
    };
    listWithdraws();
  } catch (error) {
    withdrawResponse.value = {
      status: error.response?.status || 500,
      statusText: error.response?.statusText || 'Error',
      data: error.response?.data || error.message
    };
  } finally {
    loading.value = false;
  }
};

const listWithdraws = async () => {
  if (!token.value) return;
  loading.value = true;
  try {
    const response = await axios.get('/api/v1/withdraws', {
      headers: getAuthHeaders()
    });
    withdrawList.value = response.data.data || [];
  } catch (error) {
    console.error('Erro ao listar saques:', error);
    withdrawList.value = [];
  } finally {
    loading.value = false;
  }
};

const showWithdrawDetail = async (id) => {
  selectedWithdrawId.value = id;
  loading.value = true;
  withdrawDetailResponse.value = null;
  try {
    const response = await axios.get(`/api/v1/withdraws/${id}`, {
      headers: getAuthHeaders()
    });
    withdrawDetailResponse.value = {
      status: response.status,
      statusText: response.statusText,
      data: response.data
    };
  } catch (error) {
    withdrawDetailResponse.value = {
      status: error.response?.status || 500,
      statusText: error.response?.statusText || 'Error',
      data: error.response?.data || error.message
    };
  } finally {
    loading.value = false;
  }
};

// Payment actions
const processPayment = async () => {
  loading.value = true;
  paymentResponse.value = null;
  try {
    const response = await axios.post('/api/v1/payment/process', paymentForm, {
      headers: getAuthHeaders()
    });
    paymentResponse.value = {
      status: response.status,
      statusText: response.statusText,
      data: response.data
    };
  } catch (error) {
    paymentResponse.value = {
      status: error.response?.status || 500,
      statusText: error.response?.statusText || 'Error',
      data: error.response?.data || error.message
    };
  } finally {
    loading.value = false;
  }
};

// Initial load
onMounted(() => {
  if (token.value) {
    listPix();
    listWithdraws();
  }
});
</script>


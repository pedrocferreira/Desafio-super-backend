<h1 align="center">🚀 Super-backend · Integração Multi-Subadquirentes</h1>

<p align="center">
API Laravel 10 + PHP 8.3 para integração com subadquirentes de pagamento (PIX e Saques).<br>
Focada em arquitetura limpa, Strategy Pattern e processamento assíncrono.
</p>

---

## ✨ Características

- **Autenticação** com Laravel Sanctum
- **PIX e Saques** com suporte a múltiplas subadquirentes
- **Webhooks simulados** com Laravel Jobs
- **Strategy Pattern** para fácil extensão
- **Documentação interativa** com Scribe (`/docs`)
- **Testes automatizados** (Feature + Unit)

---

## 🧱 Stack Tecnológica

- **PHP 8.3** · **Laravel 10** · **MySQL 8**
- Sanctum · Eloquent · Queues/Jobs
- Guzzle HTTP · Scribe

---

## ⚙️ Instalação

### 1. Clone e instale dependências

```bash
git clone <seu-repositorio>
cd super-backend

composer install
```

### 2. Configure o ambiente

```bash
cp .env.example .env
php artisan key:generate
```

Configure o MySQL no `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=super_backend
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha
```

### 3. Execute as migrations e seeders

```bash
php artisan migrate --seed
```

Isso criará:
- Todas as tabelas necessárias
- Usuário admin: `admin@admin.com` / `admin1234`
- Alguns usuários de teste

### 4. Inicie o servidor

```bash
php artisan serve
```

Acesse:
- **API:** `http://localhost:8000/api/v1`
- **Documentação:** `http://localhost:8000/docs`

---

## 🔐 Autenticação

### 1. Registrar novo usuário

```bash
POST /api/v1/auth/register
{
  "name": "João Silva",
  "email": "joao@example.com",
  "password": "senha123",
  "password_confirmation": "senha123"
}
```

### 2. Login

```bash
POST /api/v1/auth/login
{
  "email": "admin@admin.com",
  "password": "admin1234"
}
```

Copie o `token` retornado.

### 3. Use o token

Em todos os endpoints protegidos, envie o header:
```
Authorization: Bearer {seu_token}
```

---

## 🌐 Endpoints Principais

| Método | Rota | Descrição | Auth |
|--------|------|-----------|------|
| POST | `/api/v1/auth/register` | Registrar usuário | Não |
| POST | `/api/v1/auth/login` | Login | Não |
| POST | `/api/v1/auth/logout` | Logout | Sim |
| GET | `/api/v1/user` | Dados do usuário | Sim |
| POST | `/api/v1/pix` | Gerar PIX | Sim |
| GET | `/api/v1/pix` | Listar PIX | Sim |
| GET | `/api/v1/pix/{id}` | Detalhar PIX | Sim |
| POST | `/api/v1/withdraws` | Solicitar saque | Sim |
| GET | `/api/v1/withdraws` | Listar saques | Sim |
| GET | `/api/v1/withdraws/{id}` | Detalhar saque | Sim |

---

## 💡 Exemplos de Uso

### Gerar PIX

```bash
POST /api/v1/pix
Authorization: Bearer {token}
Content-Type: application/json

{
  "amount": 100.50,
  "payer_name": "João Silva",
  "payer_document": "12345678900",
  "description": "Pagamento teste",
  "subadquirente": "subadq_a"  // ou "subadq_b"
}
```

**Resposta:**
```json
{
  "message": "PIX gerado com sucesso.",
  "data": {
    "id": 1,
    "pix_id": "SP_SUBADQA_...",
    "qr_code": "00020126...",
    "qr_code_base64": "MDAwMjAxMjY...",
    "status": "pending",
    "amount": "100.50"
  }
}
```

### Solicitar Saque

```bash
POST /api/v1/withdraws
Authorization: Bearer {token}
Content-Type: application/json

{
  "amount": 50.00,
  "bank_account": {
    "bank": "Nubank",
    "agency": "0001",
    "account": "1234567-8",
    "account_type": "checking"
  },
  "subadquirente": "subadq_a"
}
```

---

## ⚙️ Configuração de Subadquirentes

As configurações das subadquirentes estão em `config/subadquirentes.php`.

### Variáveis de Ambiente

```env
# SubadqA (Postman Mock)
SUBADQ_A_BASE_URL=https://0acdeaee-1729-4d55-80eb-d54a125e5e18.mock.pstmn.io

# SubadqB (Postman Mock)
SUBADQ_B_BASE_URL=https://ef8513c8-fd99-4081-8963-573cd135e133.mock.pstmn.io

# Delay dos webhooks simulados (segundos)
SUBADQ_WEBHOOK_DELAY_SECONDS=3
```

---

## 🧪 Testes

### Executar todos os testes

```bash
php artisan test
```

### Executar testes específicos

```bash
php artisan test --filter=PixAndWithdrawFlowTest
```

Os testes incluem:
- ✅ Autenticação (register, login, logout)
- ✅ Criação e processamento de PIX
- ✅ Criação e processamento de Saques
- ✅ Webhooks assíncronos
- ✅ Validações e tratamento de erros

---

## 🏗️ Arquitetura

### Padrões Utilizados

- **Strategy Pattern**: Para gateways de pagamento
- **Service Layer**: Lógica de negócio isolada
- **Repository Pattern**: Via Eloquent ORM
- **Job Queue**: Processamento assíncrono de webhooks
- **SOLID Principles**: Aplicados em toda a aplicação

### Estrutura de Diretórios

```
app/
├── Enums/              # Enums (Status, Types)
├── Http/
│   ├── Controllers/    # Controllers (thin)
│   └── Requests/       # Form Requests (validação)
├── Jobs/               # Jobs assíncronos
├── Models/             # Eloquent Models
├── Providers/          # Service Providers
└── Services/           # Lógica de negócio
    ├── Payments/       # Payment services
    └── Subadquirentes/ # Gateway integrations
        ├── Contracts/  # Interfaces
        └── Gateways/   # Implementações
```

---

## 🔄 Como Funciona

### 1. Geração de PIX

1. Usuário faz `POST /api/v1/pix`
2. `PixService` resolve a subadquirente do usuário
3. Gateway (`SubadqAGateway` ou `SubadqBGateway`) cria o PIX
4. PIX salvo no banco com status `pending`
5. Job `SimulatePixWebhookJob` agendado (delay 3s)
6. Job processa webhook, atualiza status e credita saldo

### 2. Solicitação de Saque

1. Usuário faz `POST /api/v1/withdraws`
2. `WithdrawService` valida saldo disponível
3. Saldo é **reservado imediatamente** (lock pessimista)
4. Gateway cria o saque na subadquirente
5. Saque salvo no banco com status `pending`
6. Job `SimulateWithdrawWebhookJob` agendado (delay 3s)
7. Job processa webhook e atualiza status

---

## 🔌 Adicionar Nova Subadquirente

### 1. Criar Gateway

```php
// app/Services/Subadquirentes/Gateways/SubadqCGateway.php
class SubadqCGateway implements SubadquirenteGatewayInterface
{
    public function getName(): string
    {
        return 'subadq_c';
    }

    public function createPix(array $payload): array
    {
        // Implementação específica
    }

    public function createWithdraw(array $payload): array
    {
        // Implementação específica
    }

    // ... outros métodos
}
```

### 2. Registrar no Service Provider

```php
// app/Providers/SubadquirenteServiceProvider.php
public function register(): void
{
    $this->app->bind('subadquirente.subadq_c', fn () => new SubadqCGateway());
}
```

### 3. Configurar

```php
// config/subadquirentes.php
'subadq_c' => [
    'base_url' => env('SUBADQ_C_BASE_URL'),
    // ... configurações
],
```

---

## 📚 Documentação Interativa

Acesse `http://localhost:8000/docs` para:
- Ver todos os endpoints disponíveis
- Testar diretamente pelo navegador
- Copiar exemplos de código em várias linguagens
- Entender parâmetros e respostas

---

## 🚀 Queue Worker (Produção)

Para processar jobs assíncronos em produção:

### 1. Configure o driver

```env
QUEUE_CONNECTION=database
```

### 2. Execute o worker

```bash
php artisan queue:work
```

### 3. Configure supervisor (recomendado)

```ini
[program:super-backend-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
numprocs=2
```

---

## 🛠️ Comandos Úteis

```bash
# Limpar caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Recriar banco de dados
php artisan migrate:fresh --seed

# Ver rotas
php artisan route:list

# Gerar documentação Scribe
php artisan scribe:generate

# Executar testes
php artisan test

# Processar jobs manualmente
php artisan queue:work --once
```

---

## 🔒 Segurança

### Configurações Implementadas

O projeto inclui diversas camadas de segurança:

#### 1. **CORS Restrito**
- Apenas origens específicas permitidas
- Configurável via `CORS_ALLOWED_ORIGINS`
- Métodos e headers limitados

#### 2. **Tokens com Expiração**
- Tokens expiram em 60 minutos (padrão)
- Configurável via `SANCTUM_EXPIRATION`
- Reduz riscos de tokens roubados

#### 3. **Rate Limiting**
- Login/Registro: 10 requisições/minuto
- Endpoints gerais: 60 requisições/minuto
- PIX: 20 requisições/minuto
- Saques: 10 requisições/minuto
- Pagamentos: 30 requisições/minuto

#### 4. **Security Headers**
- Anti-XSS (`X-XSS-Protection`)
- Anti-clickjacking (`X-Frame-Options`)
- Anti-MIME-sniffing (`X-Content-Type-Options`)
- HTTPS forçado em produção (`Strict-Transport-Security`)
- Content Security Policy

#### 5. **Validações Robustas**
- Validação de CPF/CNPJ com dígitos verificadores
- Valores monetários limitados
- Sanitização de inputs

### Configuração de Segurança (.env)

```env
# Security Settings
SANCTUM_EXPIRATION=60
CORS_ALLOWED_ORIGINS=http://localhost:3000,http://localhost:8000
```

### Para Produção

```env
APP_ENV=production
APP_DEBUG=false
SANCTUM_EXPIRATION=30
CORS_ALLOWED_ORIGINS=https://seudominio.com
```

---

## 📝 Notas Importantes

### Fallback Inteligente

O sistema possui fallback automático quando os mocks do Postman não estão disponíveis. Ele gera dados simulados localmente, permitindo desenvolvimento sem dependências externas.

### Metadata

Todas as respostas incluem `metadata` indicando:
- `mock_used`: `true` se usou o mock do Postman
- `fallback`: `true` se usou dados simulados localmente

### Logs

Quando o fallback é acionado, um log de warning é gerado:
```
Mock SubadqA não disponível, usando fallback
```

---

## 🧩 Subadquirentes Disponíveis

| Subadquirente | Documentação | Base URL |
|---------------|--------------|----------|
| SubadqA | [Ver docs](https://documenter.getpostman.com/view/49994027/2sB3WvMJ8p) | `https://0acdeaee-1729-4d55-80eb-d54a125e5e18.mock.pstmn.io` |
| SubadqB | [Ver docs](https://documenter.getpostman.com/view/49994027/2sB3WvMJD7) | `https://ef8513c8-fd99-4081-8963-573cd135e133.mock.pstmn.io` |

---

## 🤝 Contribuindo

1. Fork o projeto
2. Crie uma branch para sua feature (`git checkout -b feature/NovaSubadquirente`)
3. Commit suas mudanças (`git commit -m 'Adiciona SubadqC'`)
4. Push para a branch (`git push origin feature/NovaSubadquirente`)
5. Abra um Pull Request

---

## 📄 Licença

Este projeto foi desenvolvido como parte de um desafio técnico.

---

## 🏆 Créditos

Desenvolvido com ❤️ demonstrando:
- Arquitetura limpa e extensível
- Boas práticas do Laravel
- Testes automatizados
- Documentação completa

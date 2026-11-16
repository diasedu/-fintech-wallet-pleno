# Fintech Wallet

Carteira digital simples: o usuário se registra, autentica, deposita e saca valores, e consulta o histórico de movimentações com o saldo após cada operação.

Monorepo com dois projetos independentes:

```
api/   Backend em Laravel (API REST)
web/   Frontend em Vue 3 (SPA)
```

## Visão geral

- **Cadastro e login** de usuário, com senha com hash (`bcrypt`).
- **Depósito e saque**, com validação de saldo e persistência atômica (saldo + movimentação na mesma transação de banco).
- **Histórico de movimentações** paginado, com filtro por tipo e período, exibindo o saldo acumulado após cada lançamento.
- **Totais do mês** (depositado/sacado) exibidos na área logada.

## Dependências

### Backend (`api/`)
- **PHP** `^8.4`
- `laravel/framework` `^13.17` — o framework.
- `laravel/sanctum` `^4.0` — autenticação via token de API (ver [Decisões técnicas](#backend)).
- `laravel/tinker` `^3.0` — REPL do Laravel.
- `ext-bcmath` — precisão decimal nas operações de saldo.
- **Dev**: `phpunit/phpunit` `^12.5` (testes), `fakerphp/faker` `^1.23` (dados falsos nas factories), `mockery/mockery` `^1.6`, `laravel/pint` `^1.27` (lint), `laravel-lang/common` `^6.9` (tradução de mensagens de validação), `laravel/pail` `^1.2`, `laravel/pao` `^1.0`, `nunomaduro/collision` `^8.6`.

### Frontend (`web/`)
- **Node** `^22.18 || >=24.12`
- `vue` `^3.5` — framework da SPA.
- `vue-router` `^5.5` — rotas (login, área logada e suas telas).
- `pinia` `^4.1` — estado da carteira (`stores/carteira.ts`).
- `bootstrap` `^5.3` — layout e componentes visuais.
- `@fortawesome/fontawesome-free` `^7.3` — ícones.
- **Dev**: `vite` `^8.2` (build/dev server), `typescript` `~6.0` + `vue-tsc` `^3.3` (checagem de tipos), `@vitejs/plugin-vue` `^6.0`, `vite-plugin-vue-devtools` `^8.2`, `npm-run-all2` `^9.0` (usado no `npm run build`).

### Raiz (`package.json`)
- `concurrently` `^9.1` — sobe `api/` e `web/` juntos com um único `npm run dev` (ver `scripts/setup.mjs` e [Decisões técnicas](#backend)).

## Decisões técnicas
### Monorepo
Facilitar a instalação para rodar localmente.

### Backend
- **Models com responsabilidade única**: cada model só sabe sobre os próprios dados — `UsuarioModel` (autenticação/saldo do usuário) e `MovimentacaoModel` (fatos de depósito/saque e as consultas de histórico/saldo acumulado). Regra de negócio que atravessa mais de um model (ex.: lançar uma movimentação e atualizar o saldo do usuário) não fica em nenhum dos dois: vai para um service (`CarteiraService`, `AutenticaService`, `CadastroUsuarioService`).
- **Controllers finos**: `Login`, `Principal` e `Carteira` só validam a entrada (via `FormRequest`) e delegam para o service correspondente, devolvendo a resposta formatada. Nenhuma regra de negócio é escrita no controller — isso mantém a lógica testável e reaproveitável fora do contexto HTTP.
- **Autenticação via token (Laravel Sanctum, modo API token)**, não via cookie de sessão. O frontend guarda o token no `localStorage` e envia `Authorization: Bearer <token>` em cada requisição (`web/src/services/api.ts`). Isso evita a complexidade de CSRF/cookies cross-domain, já que frontend e backend podem (e no deploy atual, ficam) em domínios diferentes.
- **E-mail precisa ter domínio válido** (`email:rfc,dns` em `RegistrarUsuarioRequest` e `AutenticarRequest`): além do formato (RFC), o Laravel resolve o domínio via DNS no momento da validação. Evita cadastro com e-mail de domínio inexistente/digitado errado, que nunca receberia nada enviado para ele.
- **Regra de depósito/saque isolada em services** (`api/app/Services/Carteira/CarteiraService.php`): uma classe abstrata com o fluxo comum (validar tipo, calcular novo saldo, validar regra, persistir em transação) e duas subclasses (`DepositoService`, `SaqueService`) que só definem a regra específica de cada operação. Evita duplicar a lógica transacional entre depósito e saque.
- **Saldo após cada movimentação calculado via window function SQL** (`SUM(...) OVER (ORDER BY data, id)`) em `MovimentacaoModel::listarMovimentacoes`, em vez de guardar o saldo em cada linha. Mantém a tabela de movimentações só com os fatos (tipo, valor, data) e evita inconsistência entre saldo gravado e saldo real.
- **Rate limiting dedicado para operações que movem dinheiro** (`throttle:ratelimit`, 5 requisições/min por usuário autenticado ou IP) em depósito/saque, separado do limite geral (40/min) das demais rotas da área logada (`api/routes/api.php`, `AppServiceProvider`).
- **Saldo fora do `$fillable`** do model de usuário (`UsuarioModel`): só pode ser alterado pelas regras de negócio (services de carteira), nunca por mass assignment vindo de request.
- **CORS configurável por ambiente** (`api/config/cors.php` + `CORS_ALLOWED_ORIGINS`), já que frontend e backend foram desenvolvidos como serviços separados e o domínio de origem muda entre ambientes (local, produção).
- **URL do backend configurável pelo frontend** (`VITE_API_BASE_URL`, lida em `web/src/services/api.ts`), para não deixar o domínio da API "hardedcoded" — necessário porque cada ambiente de deploy tem um domínio próprio.
- **Senha forte de acordo com padrões estabelecidos**: mínimo de 12 caracteres e obrigatoriedade de letra minúscula, letra maiúscula, número e caractere especial (critério acima do mínimo comum de mercado, reduzindo exposição a ataques de força bruta e dicionário).
- **Regra de senha forte definida uma única vez e propagada automaticamente para o frontend**: `App\Support\PoliticaSenha` concentra o critério (tamanho mínimo + regex de complexidade); `App\Rules\SenhaForte` é a regra genérica que qualquer validação de senha forte do backend deve usar, em vez de redefinir regex/min em cada `FormRequest`. O endpoint `GET /senha/politica` expõe essa mesma regra, e o frontend (`web/src/composables/usePoliticaSenha.ts`) a consome para montar o `RegExp`, a barra de progresso e a checklist de critérios do formulário de cadastro — sem duplicar a regra em JS. Se o backend mudar o critério, o frontend acompanha automaticamente, sem precisar de deploy coordenado.

### Frontend
- **Stack do frontend**: Vue 3 + Vite + TypeScript, Pinia para estado da carteira, Vue Router para as telas da área logada, Bootstrap 5 (por conta da familiaridade) para layout/componentes visuais.
- **Interface**: Interface com as separações de saque e depósito para evitar confusões.
- **Feedback de sucesso/erro via toast** (`useToast.ts` + `ToastContainer.vue`, acionado automaticamente por `api.ts` a cada requisição): em vez de alertas fixos ocupando espaço no layout, a mensagem aparece, some sozinha e não exige ação do usuário para fechar — mantém a interface limpa mesmo com várias operações (depósito, saque, login) gerando feedback com frequência.

### Banco de dados
- **Banco de dados configurável por ambiente**: localmente usa SQLite (zero setup, só `touch` do arquivo), em produção (deploy) usa MySQL. Como o acesso ao banco passa inteiramente pelo Eloquent/query builder (sem SQL específico de um driver, só a window function em `MovimentacaoModel`, suportada por ambos), a troca é só configuração (`DB_CONNECTION` e variáveis relacionadas em `.env`), sem mudança de código.

## Como rodar localmente

Pré-requisitos: PHP 8.4+, Composer, Node 22+ (ou 24+), npm.

### Opção rápida: script único (raiz do repositório)

```sh
npm install     # instala o concurrently, usado só para rodar os dois servidores juntos
npm run setup   # composer install (api) + npm install (web) + .env + sqlite + migrate + seed
npm run dev     # sobe api (http://127.0.0.1:8000) e web (http://localhost:5173) juntos
```

`npm run setup` só precisa ser executado uma vez (ou de novo após alterar dependências/migrations).

### Opção manual (cada projeto separado)

#### Backend (`api/`)

```sh
cd api
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite   # a app usa SQLite por padrão (DB_CONNECTION=sqlite)
php artisan migrate
php artisan db:seed              # cria o usuário seed (ver seção Testes)
php artisan serve                # http://127.0.0.1:8000
```

#### Frontend (`web/`)

```sh
cd web
npm install
cp .env.example .env
# Para dev local, deixe VITE_API_BASE_URL comentado: as chamadas usam "/api",
# que o Vite redireciona para http://127.0.0.1:8000 (ver vite.config.ts).
npm run dev                      # http://localhost:5173
```

Para apontar o frontend para um backend remoto (ex.: ambiente de deploy), defina no `web/.env`:

```
VITE_API_BASE_URL=https://<domínio-do-backend>/api
```

e, no backend, libere a origem do frontend em `api/.env`:

```
CORS_ALLOWED_ORIGINS=https://<domínio-do-frontend>
```

## Testes

```sh
cd api
php artisan test
```

`tests/Feature/MovimentacaoCarteiraTest.php` cobre os cenários críticos de depósito/saque (a parte que move dinheiro de fato):

- Depósito válido atualiza o saldo.
- Saque com saldo insuficiente é rejeitado e não altera o saldo.
- Saque igual ao saldo total é permitido e zera o saldo (caso de borda).
- Valor inválido (zero/negativo) é rejeitado tanto no depósito quanto no saque.
- Saldo acumulado do histórico (`saldo_apos`, calculado via window function) reflete corretamente uma sequência de movimentações.
- Depositar/sacar sem autenticação retorna 401.

Cada teste usa `RefreshDatabase` (banco SQLite em memória, configurado em `phpunit.xml`) e `UsuarioModel::factory()` para criar o usuário de teste — nenhum depende de dados seedados.

### Usuário seed

`database/seeders/DatabaseSeeder.php` cria um usuário com login fixo, para já existir uma conta pronta em dev/demo sem passar pela tela de cadastro. Já roda automaticamente no `npm run setup` e na opção manual acima (`php artisan db:seed`); é idempotente (`firstOrCreate`), então rodar de novo não duplica nem dá erro.

**Credenciais:**
- E-mail: `demo@gmail.com`
- Senha: `Demo@Senha123!`

## Deploy

- Link: https://fintech-wallet-pleno-production-cbbe6760.up.railway.app
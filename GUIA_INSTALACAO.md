# Guia de Instalação e Execução - Movie Manager

## 📋 Visão Geral

Este documento fornece instruções completas para configurar e executar o Movie Manager localmente utilizando Docker e Docker Compose.

## 🐳 Pré-requisitos

Antes de começar, certifique-se de ter instalado em sua máquina:

- **Docker** (versão 20.10 ou superior)
- **Docker Compose** (versão 1.29 ou superior)
- **Git** (para clonar o repositório)

### Verificar Instalação

```bash
# Verificar Docker
docker --version

# Verificar Docker Compose
docker-compose --version
```

## 🚀 Instalação e Configuração

### Passo 1: Clonar o Repositório

```bash
git clone https://github.com/eduardomacedo1998/The_Movie_Database.git
cd The_Movie_Database
```

### Passo 2: Configurar Variáveis de Ambiente

1. Copie o arquivo de exemplo para criar seu `.env`:

```bash
cp .env.example .env
```

2. Configure as seguintes variáveis no arquivo `.env`:

```env
# Configurações da Aplicação
APP_NAME="Movie Manager"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Chave da Aplicação (será gerada automaticamente)
APP_KEY=

# Configurações do Banco de Dados PostgreSQL
DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=laravel
DB_USERNAME=laravel
DB_PASSWORD=password

# Chave da API TMDB (OBRIGATÓRIA)
TMDB_API_KEY=03474f2e15580abb4ad3ddf6ef7b09bc
```

> **⚠️ IMPORTANTE:** A chave TMDB `03474f2e15580abb4ad3ddf6ef7b09bc` já está configurada no arquivo `.env` fornecido.

### Passo 3: Construir e Iniciar os Containers

```bash
# Construir e iniciar todos os serviços
docker-compose up --build -d
```

Este comando irá:
- Construir a imagem da aplicação Laravel
- Iniciar o container da aplicação na porta 8000
- Iniciar o container PostgreSQL na porta 5432
- Criar volumes persistentes para o banco de dados

### Passo 4: Gerar Chave da Aplicação

Após os containers estarem rodando, execute o comando para gerar a chave da aplicação:

```bash
# Acessar o container da aplicação
docker-compose exec app php artisan key:generate
```

### Passo 5: Configurar o Banco de Dados

#### Opção A: Executar Migrations (Recomendado)

```bash
# Executar migrations para criar as tabelas
docker-compose exec app php artisan migrate
```

#### Opção B: Importar Dump SQL (Alternativo)

Se você possui um arquivo de dump SQL, pode importá-lo diretamente:

```bash
# Copiar o arquivo dump.sql para o container do banco
docker cp dump.sql movie_manager_db:/tmp/dump.sql

# Importar o dump no PostgreSQL
docker-compose exec db psql -U laravel -d laravel -f /tmp/dump.sql
```

### Passo 6: Executar Seeds (Opcional)

Se desejar popular o banco com dados de exemplo:

```bash
# Executar seeds
docker-compose exec app php artisan db:seed
```

## 🌐 Acesso à Aplicação

Após completar todos os passos acima, a aplicação estará disponível em:

**URL:** http://localhost:8000

### Primeiro Acesso

1. Acesse http://localhost:8000
2. Você será redirecionado para a página de login
3. Clique em "Registrar" para criar sua primeira conta
4. Após o registro, você será logado automaticamente

## 📊 Estrutura do Banco de Dados

### Tabelas Criadas pelas Migrations

#### `users` - Usuários do Sistema
- `id` (BIGINT, PRIMARY KEY)
- `name` (VARCHAR)
- `email` (VARCHAR, UNIQUE)
- `email_verified_at` (TIMESTAMP, NULLABLE)
- `password` (VARCHAR)
- `remember_token` (VARCHAR, NULLABLE)
- `created_at` (TIMESTAMP)
- `updated_at` (TIMESTAMP)

#### `favorites` - Filmes Favoritados
- `id` (BIGINT, PRIMARY KEY)
- `user_id` (BIGINT, FOREIGN KEY → users.id)
- `tmdb_id` (VARCHAR, INDEX)
- `title` (VARCHAR)
- `poster_path` (VARCHAR, NULLABLE)
- `release_date` (DATE, NULLABLE)
- `genres` (JSON, NULLABLE)
- `created_at` (TIMESTAMP)
- `updated_at` (TIMESTAMP)

**Constraints:**
- UNIQUE KEY: `(user_id, tmdb_id)` - Impede duplicatas
- FOREIGN KEY: `user_id` → `users.id` (CASCADE DELETE)

## 🛣️ Rotas Disponíveis

### Rotas Públicas (Sem Autenticação)

| Método | Rota | Controller | Descrição |
|--------|------|------------|-----------|
| GET | `/` | - | Redireciona para `/login` |
| GET | `/login` | `AuthController@showLoginForm` | Exibe formulário de login |
| POST | `/login` | `AuthController@login` | Processa login |
| GET | `/register` | `AuthController@showRegisterForm` | Exibe formulário de registro |
| POST | `/register` | `AuthController@register` | Processa registro |

### Rotas Protegidas (Requer Autenticação)

| Método | Rota | Controller | Descrição | Payload |
|--------|------|------------|-----------|---------|
| GET | `/home` | `MovieController@index` | Página inicial com filmes | Query Params: `genre`, `year`, `vote_average_gte`, `sort_by`, `page` |
| GET | `/search` | `MovieController@search` | Busca filmes | Query Params: `q`, `genre`, `year`, `vote_average_gte`, `sort_by`, `page` |
| GET | `/favorites` | `MovieController@favorites` | Lista favoritos | Query Params: `genre` |
| POST | `/favorites/{tmdbId}` | `MovieController@addFavorite` | Adicionar favorito | Nenhum (tmdbId na URL) |
| DELETE | `/favorites/{id}` | `MovieController@removeFavorite` | Remover favorito | Nenhum (id na URL) |
| POST | `/logout` | `AuthController@logout` | Logout do usuário | Nenhum |

### Parâmetros de Query Disponíveis

#### Filtros de Filmes
- `genre` (int): ID do gênero na TMDB
- `year` (int): Ano de lançamento
- `vote_average_gte` (float): Nota mínima (0.0 - 10.0)
- `sort_by` (string): Ordenação
  - `popularity.desc` (padrão)
  - `vote_average.desc`
  - `release_date.desc`
  - `title.asc`
- `page` (int): Página da paginação (padrão: 1)

#### Busca
- `q` (string): Termo de busca por nome do filme

## 🛠️ Comandos Úteis para Desenvolvimento

### Gerenciamento de Containers

```bash
# Ver status dos containers
docker-compose ps

# Ver logs da aplicação
docker-compose logs app

# Ver logs do banco de dados
docker-compose logs db

# Parar todos os containers
docker-compose down

# Parar e remover volumes
docker-compose down -v

# Reiniciar containers
docker-compose restart
```

### Comandos Laravel dentro do Container

```bash
# Acessar o container da aplicação
docker-compose exec app bash

# Dentro do container, executar comandos Laravel
php artisan migrate:status
php artisan migrate:rollback
php artisan tinker
php artisan cache:clear
php artisan config:clear
php artisan route:list
```

### Backup e Restauração do Banco

```bash
# Criar backup do banco
docker-compose exec db pg_dump -U laravel -d laravel > backup.sql

# Restaurar backup
docker-compose exec db psql -U laravel -d laravel < backup.sql
```

## 🔧 Solução de Problemas

### Problema: Porta 8000 já está em uso

```bash
# Alterar a porta no docker-compose.yml
ports:
  - "8001:80"  # Mude para outra porta disponível
```

### Problema: Erro de conexão com o banco

```bash
# Verificar se o container do banco está rodando
docker-compose ps

# Ver logs do banco
docker-compose logs db

# Reiniciar apenas o banco
docker-compose restart db
```

### Problema: Erro na API TMDB

```bash
# Verificar se a chave TMDB_API_KEY está configurada corretamente
docker-compose exec app php artisan tinker
>>> config('services.tmdb.api_key')
```

### Problema: Permissões de arquivo

```bash
# Corrigir permissões dentro do container
docker-compose exec app chown -R www-data:www-data /var/www/html/storage
docker-compose exec app chmod -R 755 /var/www/html/storage
```

## 📝 Notas Adicionais

- **TMDB API Key**: A chave fornecida é para desenvolvimento. Para produção, obtenha sua própria chave em [TMDB API](https://www.themoviedb.org/settings/api)
- **Portas**: A aplicação roda na porta 8000. Certifique-se de que ela esteja disponível
- **Volumes**: O volume `postgres_data` persiste os dados do banco entre reinicializações
- **Performance**: Para melhor performance em desenvolvimento, considere usar volumes para `vendor/` e `node_modules/`

## 🎯 Próximos Passos

Após a instalação bem-sucedida:

1. **Explore a aplicação**: Navegue pelas páginas e teste as funcionalidades
2. **Personalize**: Modifique estilos, adicione funcionalidades
3. **Teste**: Execute `php artisan test` para rodar os testes
4. **Deploy**: Configure para produção quando estiver pronto

---

**🎬 Movie Manager - Pronto para uso!**
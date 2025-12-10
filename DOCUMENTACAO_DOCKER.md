# Configuração Docker com Volume para Laravel e PostgreSQL

Este documento descreve o passo a passo para configurar um ambiente Docker com Laravel e PostgreSQL, incluindo a criação de um volume para persistir os dados do banco.

## Pré-requisitos

- Docker e Docker Compose instalados no sistema.
- Projeto Laravel existente (neste caso, Laravel 9).

## Passos

### 1. Criar o Dockerfile

Crie um arquivo `Dockerfile` na raiz do projeto com o seguinte conteúdo:

```dockerfile
FROM php:8.0-apache

# Instalar dependências do sistema
RUN apt-get update && apt-get install -y \
    libpq-dev \
    git \
    unzip \
    && docker-php-ext-install pdo pdo_pgsql

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copiar arquivos do projeto
COPY . /var/www/html

# Definir permissões
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Configurar Apache para usar /var/www/html/public como DocumentRoot
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf \
    && sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/apache2.conf

# Habilitar mod_rewrite
RUN a2enmod rewrite

# Instalar dependências do PHP
RUN cd /var/www/html && composer install --no-dev --optimize-autoloader

# Expor porta 80
EXPOSE 80

# Comando para iniciar Apache
CMD ["apache2-foreground"]
```

Este Dockerfile:
- Usa PHP 8.0 com Apache.
- Instala extensões para PostgreSQL.
- Copia o Composer e instala as dependências do Laravel.
- Configura o Apache para servir do diretório `public` do Laravel.
- Habilita o `mod_rewrite` para suporte a rotas amigáveis.

### 2. Criar o docker-compose.yml

Crie um arquivo `docker-compose.yml` na raiz do projeto:

```yaml
version: '3.8'

services:
  app:
    build: .
    ports:
      - "8000:80"
    volumes:
      - .:/var/www/html
    depends_on:
      - db
    environment:
      - APP_ENV=local
      - DB_CONNECTION=pgsql
      - DB_HOST=db
      - DB_PORT=5432
      - DB_DATABASE=laravel
      - DB_USERNAME=laravel
      - DB_PASSWORD=password

  db:
    image: postgres:13
    environment:
      POSTGRES_DB: laravel
      POSTGRES_USER: laravel
      POSTGRES_PASSWORD: password
    volumes:
      - postgres_data:/var/lib/postgresql/data

volumes:
  postgres_data:
```

Este arquivo:
- Define o serviço `app` que constrói a imagem do Laravel e mapeia a porta 8000.
- Define o serviço `db` com PostgreSQL 13.
- Cria um volume nomeado `postgres_data` para persistir os dados do banco.
- Usa bind mount para o código do projeto no serviço `app`.

### 3. Atualizar o arquivo .env

Modifique o `.env` do Laravel para usar PostgreSQL:

```dotenv
DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=laravel
DB_USERNAME=laravel
DB_PASSWORD=password
```

### 4. Criar o .dockerignore

Crie um arquivo `.dockerignore` para otimizar o build:

```
vendor/
node_modules/
.env
.git/
.gitignore
README.md
Dockerfile
docker-compose.yml
.dockerignore
```

### 5. Construir e iniciar os containers

Execute o comando:

```bash
docker-compose up -d --build
```

Isso irá:
- Construir a imagem da aplicação.
- Iniciar os containers em background.
- Criar o volume `postgres_data` se não existir.

### 6. Verificar o funcionamento

- Acesse a aplicação em `http://localhost:8000`.
- O banco está disponível na porta 5432 (apenas internamente).

### 7. Executar migrations

Após iniciar os containers, execute as migrations do Laravel:

```bash
docker-compose exec app php artisan migrate
```

Isso criará as tabelas no banco PostgreSQL.

### 8. Comandos úteis

- Parar os containers: `docker-compose down`
- Ver logs: `docker-compose logs`
- Executar comandos no container da app: `docker-compose exec app bash`
- Executar migrations: `docker-compose exec app php artisan migrate`
- Acessar o banco PostgreSQL: `docker-compose exec db psql -U laravel -d laravel`

## Troubleshooting

### Erro "You don't have permission to access this resource"

Se ao acessar `http://localhost:8000` você receber esse erro, é porque o Apache não está configurado corretamente para o Laravel. Certifique-se de que o Dockerfile inclui as linhas para configurar o `DocumentRoot` para `/var/www/html/public` e habilitar o `mod_rewrite`, como mostrado acima.

### Logs "invalid length of startup packet" no PostgreSQL

Se aparecerem logs como "invalid length of startup packet" no PostgreSQL, isso indica tentativas de conexão inválidas na porta 5432. Para resolver, remova a exposição externa da porta do banco no `docker-compose.yml` (como mostrado acima). O banco ficará acessível apenas internamente pelos containers.
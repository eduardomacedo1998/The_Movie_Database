# Projeto: Movie Manager (Laravel + TMDB)

Este documento detalha o plano de implementação para um sistema de gerenciamento de filmes favoritos, utilizando a API pública do TMDB, Laravel (via Docker/Sail) e PostgreSQL.

---

## 🛠 Tech Stack

* **Linguagem:** PHP 8.2+
* **Framework:** Laravel 10/11
* **Ambiente:** Docker & Laravel 
* **Banco de Dados:** PostgreSQL
* **Frontend:** Blade bootstrap
* **API Externa:** The Movie Database (TMDB)

---

## 📋 Funcionalidades (Escopo)

1.  **Autenticação:** Login e Registro de usuários.
2.  **Busca:** Pesquisar filmes pelo nome (Consumo de API).
3.  **Favoritar:** Salvar filmes no banco de dados local.
4.  **Listagem:** Exibir lista de favoritos.
5.  **Filtro:** Filtrar favoritos por gênero (dados locais).
6.  **Detalhes:** Visualizar sinopse, nota e data de lançamento.
7.  **Exclusão:** Remover filme dos favoritos.

---

## 🗄️ Modelagem de Dados

O banco de dados armazenará apenas os dados necessários para a listagem de favoritos e autenticação. Os detalhes profundos serão consultados em tempo real na API.

### Tabela: `users` (Padrão Laravel)
* `id`, `name`, `email`, `password`, `timestamps`.

### Tabela: `favorites`
Responsável por guardar os filmes selecionados pelo usuário.

| Coluna | Tipo | Descrição |
| :--- | :--- | :--- |
| `id` | BigInt (PK) | ID local do registro |
| `user_id` | BigInt (FK) | Vínculo com o usuário |
| `tmdb_id` | String/Int | ID do filme na API do TMDB (Unicidade) |
| `title` | String | Título do filme (para cache visual) |
| `poster_path` | String | URL parcial da imagem de capa |
| `release_date` | Date | Data de lançamento |
| `genres` | JSON / JSONB | Array de gêneros (Ex: `['Action', 'Drama']`) |
| `created_at` | Timestamp | Data que foi favoritado |

> **Nota sobre `genres`:** Utilizaremos o tipo `JSONB` do PostgreSQL para armazenar os gêneros. Isso permite filtrar filmes por gênero diretamente no SQL sem criar tabelas auxiliares complexas para dados externos.

---

## 🚀 Plano de Implementação

### Fase 1: Configuração do Ambiente (Docker)

Instalação do Laravel já configurado com PostgreSQL via Sail.

```bash
# Baixar e instalar
curl -s "[https://laravel.build/movie-manager?with=pgsql](https://laravel.build/movie-manager?with=pgsql)" | bash

# Acessar a pasta
cd movie-manager

# Iniciar containers (Alias sugerido: alias sail='./vendor/bin/sail')
./vendor/bin/sail up -d
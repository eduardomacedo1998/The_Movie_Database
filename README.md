# Documentação Técnica - Movie Manager

## Visão Geral

O Movie Manager é uma aplicação Laravel que permite aos usuários gerenciar uma lista de filmes favoritos, utilizando a API do The Movie Database (TMDB) para obter informações sobre filmes.

---

## Model: Favorite

### Descrição
O model `Favorite` representa os filmes favoritados pelos usuários no sistema. Ele armazena informações essenciais sobre o filme e mantém o relacionamento com o usuário.

### Localização
`app/Models/Favorite.php`

### Propriedades

#### Fillable Attributes
```php
protected $fillable = [
    'user_id',      // ID do usuário que favoritou
    'tmdb_id',      // ID único do filme na API TMDB
    'title',        // Título do filme
    'poster_path',  // Caminho da imagem do poster
    'release_date', // Data de lançamento
    'genres',       // Array de gêneros (JSON)
];
```

#### Casts
```php
protected $casts = [
    'genres' => 'array',           // Converte JSON para array
    'release_date' => 'date',      // Converte para objeto Carbon
];
```

### Relacionamentos

#### User (belongsTo)
```php
public function user()
{
    return $this->belongsTo(User::class);
}
```

### Métodos Estáticos

#### `isFavorite(int $userId, string $tmdbId): bool`
Verifica se um filme já está nos favoritos do usuário.

**Parâmetros:**
- `$userId` (int): ID do usuário
- `$tmdbId` (string): ID do filme na TMDB

**Retorno:** `true` se o filme estiver favoritado, `false` caso contrário

#### `addFavorite(int $userId, array $movieData): Favorite`
Adiciona um filme aos favoritos do usuário.

**Parâmetros:**
- `$userId` (int): ID do usuário
- `$movieData` (array): Dados do filme retornados pela API TMDB

**Retorno:** Instância do model Favorite criado

### Estrutura da Tabela

| Coluna | Tipo | Descrição | Restrições |
|--------|------|-----------|------------|
| `id` | BIGINT | Chave primária | AUTO_INCREMENT |
| `user_id` | BIGINT | FK para users | NOT NULL, INDEX |
| `tmdb_id` | VARCHAR | ID na TMDB | NOT NULL, INDEX |
| `title` | VARCHAR | Título do filme | NOT NULL |
| `poster_path` | VARCHAR | Caminho do poster | NULLABLE |
| `release_date` | DATE | Data de lançamento | NULLABLE |
| `genres` | JSON | Array de gêneros | NULLABLE |
| `created_at` | TIMESTAMP | Data de criação | NOT NULL |
| `updated_at` | TIMESTAMP | Data de atualização | NOT NULL |

### Constraints
- **Chave única composta:** `(user_id, tmdb_id)` - Impede duplicatas
- **Foreign Key:** `user_id` referencia `users.id` com CASCADE DELETE

---

## Controller: MovieController

### Descrição
O `MovieController` gerencia todas as operações relacionadas a filmes, incluindo listagem, busca, filtros e gerenciamento de favoritos.

### Localização
`app/Http/Controllers/MovieController.php`

### Dependências
- `TmdbService`: Serviço para integração com API TMDB
- `Favorite`: Model para filmes favoritos
- `Auth`: Facade para autenticação

### Métodos

#### `index(Request $request): View`
Página inicial que exibe filmes populares ou filtrados.

**Parâmetros:**
- `$request` (Request): Requisição HTTP com possíveis filtros

**Filtros suportados:**
- `genre` (int): ID do gênero na TMDB
- `year` (int): Ano de lançamento
- `vote_average_gte` (float): Nota mínima
- `sort_by` (string): Ordenação (popularity.desc, vote_average.desc, etc.)
- `page` (int): Página da paginação

**Retorno:** View `movies.home` com dados dos filmes

#### `search(Request $request): View`
Busca filmes por nome ou aplica filtros avançados.

**Parâmetros:**
- `$request` (Request): Requisição HTTP

**Parâmetros de busca:**
- `q` (string): Termo de busca (opcional)
- `genre`, `year`, `vote_average_gte`, `sort_by`, `page`: Mesmo que index()

**Lógica:**
- Se `q` estiver presente: busca por nome
- Se `q` estiver vazio mas filtros presentes: usa discover API
- Caso contrário: redireciona para home

**Retorno:** View `movies.search` com resultados

#### `favorites(Request $request): View|JsonResponse`
Exibe ou retorna lista de filmes favoritos do usuário.

**Parâmetros:**
- `$request` (Request): Requisição HTTP

**Filtros suportados:**
- `genre` (string): Nome do gênero para filtrar

**Retorno:**
- HTML: View `movies.favorites`
- JSON (AJAX): Lista de favoritos para verificação de estado

#### `addFavorite(string $tmdbId): JsonResponse`
Adiciona um filme aos favoritos via AJAX.

**Parâmetros:**
- `$tmdbId` (string): ID do filme na TMDB

**Processo:**
1. Verifica se já está favoritado
2. Busca detalhes na API TMDB
3. Cria registro no banco
4. Retorna resposta JSON

**Respostas possíveis:**
- Sucesso: `{"success": true, "message": "...", "favorite_id": 123}`
- Já favoritado: `{"success": false, "message": "..."}` (400)
- Erro na API: `{"success": false, "message": "..."}` (500)

#### `removeFavorite(int $id): JsonResponse`
Remove um filme dos favoritos via AJAX.

**Parâmetros:**
- `$id` (int): ID do registro de favorito

**Processo:**
1. Busca favorito do usuário atual
2. Remove registro do banco
3. Retorna resposta JSON

**Respostas possíveis:**
- Sucesso: `{"success": true, "message": "..."}`
- Não encontrado: `{"success": false, "message": "..."}` (404)

---

## Service: TmdbService

### Descrição
Serviço responsável pela integração com a API do The Movie Database (TMDB).

### Localização
`app/Services/TmdbService.php`

### Configuração
- `TMDB_API_KEY`: Chave da API (configurada em `.env`)
- `baseUrl`: `https://api.themoviedb.org/3`
- `imageBaseUrl`: `https://image.tmdb.org/t/p/w500`

### Métodos

#### `searchMovies(string $query, int $page = 1): array|null`
Busca filmes por termo de pesquisa.

#### `getMovieDetails(string $movieId): array|null`
Obtém detalhes completos de um filme específico.

#### `getGenres(): array`
Retorna lista de gêneros disponíveis.

#### `getImageUrl(string $path): string`
Gera URL completa para imagem do TMDB.

#### `getPopularMovies(int $page = 1): array|null`
Obtém filmes populares.

#### `discoverMovies(array $filters, int $page = 1): array|null`
Descobre filmes com filtros avançados.

**Filtros suportados:**
- `genre` (int): ID do gênero
- `year` (int): Ano de lançamento
- `vote_average_gte` (float): Nota mínima
- `vote_average_lte` (float): Nota máxima
- `sort_by` (string): Critério de ordenação

---

## Views

### Estrutura de Views
```
resources/views/
├── layouts/
│   └── app.blade.php          # Layout principal
├── auth/
│   ├── login.blade.php        # Formulário de login
│   └── register.blade.php     # Formulário de registro
└── movies/
    ├── home.blade.php         # Página inicial
    ├── search.blade.php       # Resultados de busca
    └── favorites.blade.php    # Lista de favoritos
```

### Funcionalidades das Views

#### Layout Principal (`layouts/app.blade.php`)
- Navbar com navegação e busca integrada
- Sistema de toasts para notificações
- JavaScript para gerenciamento de favoritos
- Design responsivo profissional

#### Páginas de Filmes
- Cards com posters, títulos e informações
- Modais para detalhes completos
- Sistema de paginação
- Filtros avançados (home/search)
- Botões de favoritar com estados visuais

---

## API Endpoints

### Rotas Públicas
```
GET  /           -> MovieController@index
GET  /login      -> AuthController@showLoginForm
POST /login      -> AuthController@login
GET  /register   -> AuthController@showRegisterForm
POST /register   -> AuthController@register
```

### Rotas Protegidas (middleware: auth)
```
GET  /home       -> MovieController@index
GET  /search     -> MovieController@search
GET  /favorites  -> MovieController@favorites
POST /favorites/{tmdbId}  -> MovieController@addFavorite
DELETE /favorites/{id}    -> MovieController@removeFavorite
POST /logout     -> AuthController@logout
```

---

## Tratamento de Erros

### Validações
- Autenticação obrigatória para operações de favoritos
- Verificação de propriedade (usuário só acessa seus favoritos)
- Validação de dados da API TMDB

### Tratamento de Exceções
- Erros da API TMDB são logados e retornam respostas amigáveis
- Falhas de rede são tratadas graciosamente
- Dados inválidos retornam mensagens de erro apropriadas

---

## Segurança

### Medidas Implementadas
- CSRF protection em todos os formulários
- Autenticação obrigatória para operações sensíveis
- Sanitização de dados de entrada
- Validação de ownership (usuário só acessa seus dados)

### Proteções Adicionais
- Rate limiting pode ser implementado no futuro
- Logs de segurança para tentativas suspeitas
- Validação de tokens de API

---

## Performance

### Otimizações
- Paginação em todas as listagens
- Cache de imagens do TMDB
- Queries otimizadas com índices apropriados
- Lazy loading de relacionamentos

### Considerações
- API TMDB tem limites de requisições
- Implementar cache local para reduzir chamadas à API
- Compressão de imagens para melhor performance

---

## Testes

### Casos de Teste Sugeridos
- Autenticação e autorização
- CRUD de favoritos
- Integração com API TMDB
- Validação de formulários
- Tratamento de erros
- Performance com grandes volumes de dados

---

## Manutenção

### Tarefas Periódicas
- Atualização da chave da API TMDB
- Limpeza de logs antigos
- Backup de dados
- Monitoramento de performance

### Monitoramento
- Logs de erro da aplicação
- Monitoramento de uso da API TMDB
- Análise de performance das queries
- Verificação de disponibilidade dos serviços externos

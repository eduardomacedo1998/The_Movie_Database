@extends('layouts.app')

@section('title', 'Início - Movie Manager')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h2 class="fw-bold mb-3">
            <i class="fas fa-film text-primary"></i> Catálogo de Filmes
        </h2>
        <p class="text-muted">Explore nossa coleção completa de filmes</p>
    </div>
</div>

<!-- Filtros Avançados -->
<div class="card mb-4 shadow-sm">
    <div class="card-header bg-light">
        <h5 class="mb-0">
            <i class="fas fa-filter"></i> Filtros Avançados
            <button class="btn btn-sm btn-link float-end" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse">
                <i class="fas fa-chevron-down"></i>
            </button>
        </h5>
    </div>
    <div class="collapse {{ !empty($filters['genre']) || !empty($filters['year']) || !empty($filters['vote_average_gte']) || !empty($filters['sort_by']) ? 'show' : '' }}" id="filterCollapse">
        <div class="card-body">
            <form method="GET" action="{{ route('home') }}" id="filterForm">
                <div class="row g-3">
                    <!-- Filtro por Gênero -->
                    <div class="col-md-3">
                        <label for="genre" class="form-label">Gênero</label>
                        <select name="genre" id="genre" class="form-select">
                            <option value="">Todos os Gêneros</option>
                            @foreach($genres as $genre)
                                <option value="{{ $genre['id'] }}" {{ ($filters['genre'] ?? '') == $genre['id'] ? 'selected' : '' }}>
                                    {{ $genre['name'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filtro por Ano -->
                    <div class="col-md-3">
                        <label for="year" class="form-label">Ano de Lançamento</label>
                        <select name="year" id="year" class="form-select">
                            <option value="">Todos os Anos</option>
                            @for($y = date('Y'); $y >= 1990; $y--)
                                <option value="{{ $y }}" {{ ($filters['year'] ?? '') == $y ? 'selected' : '' }}>
                                    {{ $y }}
                                </option>
                            @endfor
                        </select>
                    </div>

                    <!-- Filtro por Nota Mínima -->
                    <div class="col-md-3">
                        <label for="vote_average_gte" class="form-label">Nota Mínima</label>
                        <select name="vote_average_gte" id="vote_average_gte" class="form-select">
                            <option value="">Qualquer Nota</option>
                            @for($i = 9; $i >= 5; $i--)
                                <option value="{{ $i }}" {{ ($filters['vote_average_gte'] ?? '') == $i ? 'selected' : '' }}>
                                    {{ $i }}+ ⭐
                                </option>
                            @endfor
                        </select>
                    </div>

                    <!-- Ordenação -->
                    <div class="col-md-3">
                        <label for="sort_by" class="form-label">Ordenar Por</label>
                        <select name="sort_by" id="sort_by" class="form-select">
                            <option value="popularity.desc" {{ ($filters['sort_by'] ?? '') == 'popularity.desc' ? 'selected' : '' }}>
                                Mais Popular
                            </option>
                            <option value="vote_average.desc" {{ ($filters['sort_by'] ?? '') == 'vote_average.desc' ? 'selected' : '' }}>
                                Melhor Avaliação
                            </option>
                            <option value="release_date.desc" {{ ($filters['sort_by'] ?? '') == 'release_date.desc' ? 'selected' : '' }}>
                                Mais Recente
                            </option>
                            <option value="release_date.asc" {{ ($filters['sort_by'] ?? '') == 'release_date.asc' ? 'selected' : '' }}>
                                Mais Antigo
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Filtros Especiais -->
                <div class="row mt-3">
                    <div class="col-12">
                        <h6 class="mb-2">Filtros Especiais</h6>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="no_image" value="1" id="no_image"
                                {{ isset($filters['no_image']) && $filters['no_image'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="no_image">
                                <i class="fas fa-image text-muted"></i> Apenas filmes sem imagem
                            </label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="no_description" value="1" id="no_description"
                                {{ isset($filters['no_description']) && $filters['no_description'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="no_description">
                                <i class="fas fa-file-alt text-muted"></i> Apenas filmes sem descrição
                            </label>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Aplicar Filtros
                        </button>
                        <a href="{{ route('home') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i> Limpar Filtros
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@if(count($movies) > 0)
<div class="row g-4">
    @foreach($movies as $movie)
    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
        <div class="card movie-card shadow-sm">
            <div class="position-relative">
                <img src="{{ app(\App\Services\TmdbService::class)->getImageUrl($movie['poster_path'] ?? null) }}" 
                     class="movie-poster" 
                     alt="{{ $movie['title'] }}">
                
                <!-- Botão de Favorito -->
                <button class="btn-favorite" 
                        onclick="toggleFavorite({{ $movie['id'] }}, this)"
                        data-tmdb-id="{{ $movie['id'] }}"
                        data-favorite-id="">
                    <i class="far fa-heart"></i>
                </button>

                <!-- Badge de Rating -->
                @if(isset($movie['vote_average']) && $movie['vote_average'] > 0)
                <div class="rating-badge">
                    <i class="fas fa-star"></i> {{ number_format($movie['vote_average'], 1) }}
                </div>
                @endif
            </div>

            <div class="card-body">
                <h5 class="movie-title mb-2" title="{{ $movie['title'] }}">
                    {{ $movie['title'] }}
                </h5>

                <!-- Badges de dados faltantes -->
                <div class="mb-2">
                    @if(empty($movie['poster_path']))
                    <span class="badge bg-warning text-dark me-1">
                        <i class="fas fa-image"></i> Sem Imagem
                    </span>
                    @endif
                    @if(empty($movie['overview']))
                    <span class="badge bg-info text-dark">
                        <i class="fas fa-file-alt"></i> Sem Descrição
                    </span>
                    @endif
                </div>
                
                @if(isset($movie['release_date']))
                <p class="text-muted small mb-2">
                    <i class="far fa-calendar"></i> 
                    {{ \Carbon\Carbon::parse($movie['release_date'])->format('Y') }}
                </p>
                @endif

                @if(isset($movie['overview']) && !empty($movie['overview']))
                <p class="card-text small text-muted" style="height: 60px; overflow: hidden;">
                    {{ Str::limit($movie['overview'], 100) }}
                </p>
                @endif

                <!-- Modal Trigger -->
                <button class="btn btn-sm btn-outline-primary w-100" 
                        data-bs-toggle="modal" 
                        data-bs-target="#movieModal{{ $movie['id'] }}">
                    <i class="fas fa-info-circle"></i> Ver Detalhes
                </button>
            </div>
        </div>
    </div>

    <!-- Modal de Detalhes -->
    <div class="modal fade" id="movieModal{{ $movie['id'] }}" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background: #2c3e50; color: white;">
                    <h5 class="modal-title">{{ $movie['title'] }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <img src="{{ app(\App\Services\TmdbService::class)->getImageUrl($movie['poster_path'] ?? null) }}" 
                                 class="img-fluid rounded" 
                                 alt="{{ $movie['title'] }}">
                        </div>
                        <div class="col-md-8">
                            <h6 class="fw-bold mb-3">Sinopse</h6>
                            <p>{{ $movie['overview'] ?? 'Sinopse não disponível.' }}</p>
                            
                            <hr>
                            
                            <div class="row">
                                <div class="col-6">
                                    <p class="mb-2">
                                        <strong>Data de Lançamento:</strong><br>
                                        {{ isset($movie['release_date']) ? \Carbon\Carbon::parse($movie['release_date'])->format('d/m/Y') : 'N/A' }}
                                    </p>
                                </div>
                                <div class="col-6">
                                    <p class="mb-2">
                                        <strong>Avaliação:</strong><br>
                                        <span class="text-warning">
                                            <i class="fas fa-star"></i> 
                                            {{ isset($movie['vote_average']) ? number_format($movie['vote_average'], 1) : 'N/A' }}/10
                                        </span>
                                    </p>
                                </div>
                                <div class="col-12 mt-2">
                                    <p class="mb-2">
                                        <strong>Popularidade:</strong><br>
                                        {{ isset($movie['popularity']) ? number_format($movie['popularity'], 0) : 'N/A' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    <button type="button" 
                            class="btn btn-primary"
                            onclick="toggleFavorite({{ $movie['id'] }}, this); bootstrap.Modal.getInstance(document.getElementById('movieModal{{ $movie['id'] }}')).hide();">
                        <i class="fas fa-heart"></i> Adicionar aos Favoritos
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<!-- Paginação -->
@if(($totalPages ?? 1) > 1)
<div class="row mt-5">
    <div class="col-12">
        <nav>
            <ul class="pagination justify-content-center">
                <!-- Botão Anterior -->
                @if(($currentPage ?? 1) > 1)
                <li class="page-item">
                    <a class="page-link" href="{{ route('home', array_merge($filters ?? [], ['page' => ($currentPage ?? 1) - 1])) }}">
                        <i class="fas fa-chevron-left"></i> Anterior
                    </a>
                </li>
                @endif

                <!-- Páginas -->
                @for($i = max(1, ($currentPage ?? 1) - 2); $i <= min(($totalPages ?? 1), ($currentPage ?? 1) + 2); $i++)
                <li class="page-item {{ $i == ($currentPage ?? 1) ? 'active' : '' }}">
                    <a class="page-link" href="{{ route('home', array_merge($filters ?? [], ['page' => $i])) }}">
                        {{ $i }}
                    </a>
                </li>
                @endfor

                <!-- Botão Próximo -->
                @if(($currentPage ?? 1) < ($totalPages ?? 1))
                <li class="page-item">
                    <a class="page-link" href="{{ route('home', array_merge($filters ?? [], ['page' => ($currentPage ?? 1) + 1])) }}">
                        Próximo <i class="fas fa-chevron-right"></i>
                    </a>
                </li>
                @endif
            </ul>
        </nav>
    </div>
</div>
@endif

@else
<div class="alert alert-info text-center">
    <i class="fas fa-info-circle"></i> Nenhum filme encontrado com os filtros aplicados.
</div>
@endif
@endsection

@section('scripts')
<script>
    // Verificar filmes já favoritados ao carregar a página
    document.addEventListener('DOMContentLoaded', async function() {
        try {
            const response = await fetch('/favorites', {
                headers: {
                    'Accept': 'application/json'
                }
            });
            
            if (response.ok) {
                const data = await response.json();
                if (data.favorites) {
                    data.favorites.forEach(fav => {
                        const buttons = document.querySelectorAll(`[data-tmdb-id="${fav.tmdb_id}"]`);
                        buttons.forEach(btn => {
                            btn.classList.add('active');
                            btn.innerHTML = '<i class="fas fa-heart"></i>';
                            btn.dataset.favoriteId = fav.id;
                        });
                    });
                }
            }
        } catch (error) {
            console.log('Não foi possível carregar favoritos');
        }
    });
</script>
@endsection

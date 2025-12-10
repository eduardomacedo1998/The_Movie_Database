@extends('layouts.app')

@section('title', 'Início - Movie Manager')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h2 class="fw-bold mb-3">
            <i class="fas fa-fire text-danger"></i> Filmes Populares
        </h2>
        <p class="text-muted">Descubra os filmes mais populares do momento</p>
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
                <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
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
@else
<div class="alert alert-info text-center">
    <i class="fas fa-info-circle"></i> Nenhum filme encontrado.
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

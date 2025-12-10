@extends('layouts.app')

@section('title', 'Meus Favoritos - Movie Manager')

@section('content')
<div class="row mb-4">
    <div class="col-12 col-md-8">
        <h2 class="fw-bold mb-3">
            <i class="fas fa-heart text-danger"></i> Minha Lista de Favoritos
        </h2>
        <p class="text-muted">
            @if(count($favorites) > 0)
                Você tem {{ count($favorites) }} filme(s) favorito(s)
            @else
                Nenhum filme favorito ainda
            @endif
        </p>
    </div>
    
    <!-- Filtro por Gênero -->
    @if(count($genres) > 0)
    <div class="col-12 col-md-4">
        <form method="GET" action="{{ route('movies.favorites') }}">
            <div class="input-group">
                <select name="genre" class="form-select" onchange="this.form.submit()">
                    <option value="">Todos os Gêneros</option>
                    @foreach($genres as $genre)
                        <option value="{{ $genre }}" {{ $selectedGenre == $genre ? 'selected' : '' }}>
                            {{ $genre }}
                        </option>
                    @endforeach
                </select>
                @if($selectedGenre)
                <a href="{{ route('movies.favorites') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times"></i>
                </a>
                @endif
            </div>
        </form>
    </div>
    @endif
</div>

@if(count($favorites) > 0)
<div class="row g-4">
    @foreach($favorites as $favorite)
    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
        <div class="card movie-card shadow-sm">
            <div class="position-relative">
                <img src="{{ app(\App\Services\TmdbService::class)->getImageUrl($favorite->poster_path) }}" 
                     class="movie-poster" 
                     alt="{{ $favorite->title }}">
                
                <!-- Botão de Remover dos Favoritos -->
                <button class="btn-favorite active" 
                        onclick="removeFavoriteFromList({{ $favorite->id }}, this)"
                        data-favorite-id="{{ $favorite->id }}"
                        title="Remover dos favoritos">
                    <i class="fas fa-heart"></i>
                </button>

                <!-- Badge de Data de Adição -->
                <div class="rating-badge">
                    <i class="fas fa-clock"></i> {{ $favorite->created_at->diffForHumans() }}
                </div>
            </div>

            <div class="card-body">
                <h5 class="movie-title mb-2" title="{{ $favorite->title }}">
                    {{ $favorite->title }}
                </h5>
                
                @if($favorite->release_date)
                <p class="text-muted small mb-2">
                    <i class="far fa-calendar"></i> 
                    {{ $favorite->release_date->format('Y') }}
                </p>
                @endif

                @if($favorite->genres && count($favorite->genres) > 0)
                <div class="mb-3">
                    @foreach(array_slice($favorite->genres, 0, 3) as $genre)
                        <span class="badge bg-secondary me-1 mb-1" style="font-size: 0.7rem;">
                            {{ $genre }}
                        </span>
                    @endforeach
                </div>
                @endif

                <!-- Botão para Ver Detalhes -->
                <button class="btn btn-sm btn-outline-primary w-100" 
                        data-bs-toggle="modal" 
                        data-bs-target="#favoriteModal{{ $favorite->id }}">
                    <i class="fas fa-info-circle"></i> Ver Detalhes
                </button>
            </div>
        </div>
    </div>

    <!-- Modal de Detalhes -->
    <div class="modal fade" id="favoriteModal{{ $favorite->id }}" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background: #2c3e50; color: white;">
                    <h5 class="modal-title">{{ $favorite->title }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <img src="{{ app(\App\Services\TmdbService::class)->getImageUrl($favorite->poster_path) }}" 
                                 class="img-fluid rounded" 
                                 alt="{{ $favorite->title }}">
                        </div>
                        <div class="col-md-8">
                            <h6 class="fw-bold mb-3">Informações</h6>
                            
                            <div class="mb-3">
                                <strong>Título:</strong><br>
                                {{ $favorite->title }}
                            </div>

                            @if($favorite->release_date)
                            <div class="mb-3">
                                <strong>Data de Lançamento:</strong><br>
                                {{ $favorite->release_date->format('d/m/Y') }}
                            </div>
                            @endif

                            @if($favorite->genres && count($favorite->genres) > 0)
                            <div class="mb-3">
                                <strong>Gêneros:</strong><br>
                                @foreach($favorite->genres as $genre)
                                    <span class="badge bg-secondary me-1">{{ $genre }}</span>
                                @endforeach
                            </div>
                            @endif

                            <div class="mb-3">
                                <strong>Adicionado em:</strong><br>
                                {{ $favorite->created_at->format('d/m/Y \à\s H:i') }}
                                <span class="text-muted">({{ $favorite->created_at->diffForHumans() }})</span>
                            </div>

                            <hr>

                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> 
                                Para ver mais detalhes e sinopse completa, 
                                <a href="{{ route('movies.search', ['q' => $favorite->title]) }}" class="alert-link">
                                    busque pelo filme
                                </a>.
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    <button type="button" 
                            class="btn btn-danger"
                            onclick="removeFavoriteFromList({{ $favorite->id }}, this); bootstrap.Modal.getInstance(document.getElementById('favoriteModal{{ $favorite->id }}')).hide();">
                        <i class="fas fa-trash"></i> Remover dos Favoritos
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@else
<div class="text-center py-5">
    <div class="mb-4">
        <i class="fas fa-heart-broken" style="font-size: 5rem; color: #ddd;"></i>
    </div>
    <h4 class="mb-3">Nenhum filme favorito ainda</h4>
    <p class="text-muted mb-4">
        @if($selectedGenre)
            Nenhum filme encontrado com o gênero "{{ $selectedGenre }}".
            <br>
            <a href="{{ route('movies.favorites') }}" class="btn btn-sm btn-outline-secondary mt-2">
                <i class="fas fa-times"></i> Limpar Filtro
            </a>
        @else
            Adicione filmes aos seus favoritos para visualizá-los aqui.
        @endif
    </p>
    <a href="{{ route('home') }}" class="btn btn-primary">
        <i class="fas fa-search"></i> Explorar Catálogo
    </a>
</div>
@endif
@endsection

@section('scripts')
<script>
    // Função específica para remover favorito da lista
    async function removeFavoriteFromList(favoriteId, button) {
        if (!confirm('Tem certeza que deseja remover este filme dos favoritos?')) {
            return;
        }

        try {
            const response = await fetch(`/favorites/${favoriteId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                showToast(data.message, 'success');
                
                // Remover o card da página
                const card = button.closest('.col-12');
                card.style.transition = 'opacity 0.3s';
                card.style.opacity = '0';
                
                setTimeout(() => {
                    card.remove();
                    
                    // Verificar se ainda há favoritos
                    const remainingCards = document.querySelectorAll('.movie-card').length;
                    if (remainingCards === 0) {
                        location.reload();
                    }
                }, 300);
            } else {
                showToast(data.message, 'danger');
            }
        } catch (error) {
            console.error('Erro:', error);
            showToast('Erro ao remover filme dos favoritos', 'danger');
        }
    }
</script>
@endsection

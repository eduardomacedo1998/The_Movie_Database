@extends('layouts.app')

@section('title', 'Estatísticas de Filmes')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-chart-bar me-2"></i>
                        Estatísticas de Filmes
                    </h4>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4">
                        Análise estatística sobre a completude dos dados dos filmes na TMDB
                    </p>

                    <!-- Estatísticas de Filmes Populares -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="text-primary mb-3">
                                <i class="fas fa-star me-2"></i>
                                Filmes Populares
                            </h5>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h3 class="text-primary">{{ $popularStats['total'] }}</h3>
                                            <p class="mb-0">Total de Filmes</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-warning">
                                        <div class="card-body text-center">
                                            <h3 class="text-white">{{ $popularStats['without_image'] }}</h3>
                                            <p class="mb-0">Sem Imagem</p>
                                            <small class="text-white-50">{{ $popularStats['percentage_without_image'] }}%</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-info">
                                        <div class="card-body text-center">
                                            <h3 class="text-white">{{ $popularStats['without_description'] }}</h3>
                                            <p class="mb-0">Sem Descrição</p>
                                            <small class="text-white-50">{{ $popularStats['percentage_without_description'] }}%</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-danger">
                                        <div class="card-body text-center">
                                            <h3 class="text-white">{{ $popularStats['without_both'] }}</h3>
                                            <p class="mb-0">Sem Ambos</p>
                                            <small class="text-white-50">{{ $popularStats['percentage_without_both'] }}%</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Estatísticas de Descoberta -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="text-primary mb-3">
                                <i class="fas fa-search me-2"></i>
                                Filmes por Descoberta
                            </h5>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h3 class="text-primary">{{ $discoverStats['total'] }}</h3>
                                            <p class="mb-0">Total de Filmes</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-warning">
                                        <div class="card-body text-center">
                                            <h3 class="text-white">{{ $discoverStats['without_image'] }}</h3>
                                            <p class="mb-0">Sem Imagem</p>
                                            <small class="text-white-50">{{ $discoverStats['percentage_without_image'] }}%</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-info">
                                        <div class="card-body text-center">
                                            <h3 class="text-white">{{ $discoverStats['without_description'] }}</h3>
                                            <p class="mb-0">Sem Descrição</p>
                                            <small class="text-white-50">{{ $discoverStats['percentage_without_description'] }}%</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-danger">
                                        <div class="card-body text-center">
                                            <h3 class="text-white">{{ $discoverStats['without_both'] }}</h3>
                                            <p class="mb-0">Sem Ambos</p>
                                            <small class="text-white-50">{{ $discoverStats['percentage_without_both'] }}%</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Estatísticas de Favoritos -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="text-primary mb-3">
                                <i class="fas fa-heart me-2"></i>
                                Seus Favoritos
                            </h5>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h3 class="text-primary">{{ $favoritesStats['total'] }}</h3>
                                            <p class="mb-0">Total de Favoritos</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card bg-success">
                                        <div class="card-body text-center">
                                            <h3 class="text-white">{{ $favoritesStats['with_image'] }}</h3>
                                            <p class="mb-0">Com Imagem</p>
                                            <small class="text-white-50">
                                                {{ $favoritesStats['total'] > 0 ? round(($favoritesStats['with_image'] / $favoritesStats['total']) * 100, 1) : 0 }}%
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card bg-secondary">
                                        <div class="card-body text-center">
                                            <h3 class="text-white">{{ $favoritesStats['with_description'] }}</h3>
                                            <p class="mb-0">Com Descrição</p>
                                            <small class="text-white-50">
                                                {{ $favoritesStats['total'] > 0 ? round(($favoritesStats['with_description'] / $favoritesStats['total']) * 100, 1) : 0 }}%
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Informações Adicionais -->
                    <div class="row">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <h6><i class="fas fa-info-circle me-2"></i>Informações sobre os Dados</h6>
                                <ul class="mb-0">
                                    <li>As estatísticas são baseadas em amostras dos filmes mais populares e descobertos na TMDB</li>
                                    <li>Filmes sem imagem não possuem poster_path válido na API</li>
                                    <li>Filmes sem descrição não possuem overview preenchido</li>
                                    <li>Os dados são atualizados periodicamente através do cache Redis</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Botões de Ação -->
                    <div class="row">
                        <div class="col-12 text-center">
                            <a href="{{ route('home') }}" class="btn btn-primary me-2">
                                <i class="fas fa-home me-1"></i>
                                Voltar ao Início
                            </a>
                            <a href="{{ route('movies.search') }}" class="btn btn-outline-primary">
                                <i class="fas fa-search me-1"></i>
                                Buscar Filmes
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
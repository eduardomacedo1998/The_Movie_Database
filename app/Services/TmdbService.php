<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class TmdbService
{
    private $apiKey;
    private $baseUrl;
    private $imageBaseUrl;
    private const CACHE_TTL = 3600; // 1 hora em segundos

    public function __construct()
    {
        $this->apiKey = env('TMDB_API_KEY');
        $this->baseUrl = 'https://api.themoviedb.org/3';
        $this->imageBaseUrl = 'https://image.tmdb.org/t/p/w500';
    }

    /**
     * Busca filmes por nome
     */
    public function searchMovies($query, $page = 1)
    {
        $cacheKey = "tmdb_search_" . md5($query) . "_page_{$page}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($query, $page) {
            try {
                $response = Http::get("{$this->baseUrl}/search/movie", [
                    'api_key' => $this->apiKey,
                    'language' => 'pt-BR',
                    'query' => $query,
                    'page' => $page,
                ]);

                if ($response->successful()) {
                    return $response->json();
                }

                return null;
            } catch (\Exception $e) {
                Log::error('Erro ao buscar filmes: ' . $e->getMessage());
                return null;
            }
        });
    }

    /**
     * Obtém detalhes de um filme específico
     */
    public function getMovieDetails($movieId)
    {
        $cacheKey = "tmdb_movie_details_{$movieId}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($movieId) {
            try {
                $response = Http::get("{$this->baseUrl}/movie/{$movieId}", [
                    'api_key' => $this->apiKey,
                    'language' => 'pt-BR',
                ]);

                if ($response->successful()) {
                    return $response->json();
                }

                return null;
            } catch (\Exception $e) {
                Log::error('Erro ao buscar detalhes do filme: ' . $e->getMessage());
                return null;
            }
        });
    }

    /**
     * Obtém os gêneros disponíveis
     */
    public function getGenres()
    {
        return Cache::remember('tmdb_genres', self::CACHE_TTL * 24, function () {
            try {
                $response = Http::get("{$this->baseUrl}/genre/movie/list", [
                    'api_key' => $this->apiKey,
                    'language' => 'pt-BR',
                ]);

                if ($response->successful()) {
                    return $response->json()['genres'] ?? [];
                }

                return [];
            } catch (\Exception $e) {
                Log::error('Erro ao buscar gêneros: ' . $e->getMessage());
                return [];
            }
        });
    }

    /**
     * Retorna a URL completa da imagem
     */
    public function getImageUrl($path)
    {
        if (!$path) {
            return 'https://via.placeholder.com/500x750?text=Sem+Imagem';
        }
        return $this->imageBaseUrl . $path;
    }

    /**
     * Busca filmes populares
     */
    public function getPopularMovies($page = 1)
    {
        try {
            $response = Http::get("{$this->baseUrl}/movie/popular", [
                'api_key' => $this->apiKey,
                'language' => 'pt-BR',
                'page' => $page,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Erro ao buscar filmes populares: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Descobre filmes com filtros avançados
     */
    public function discoverMovies($filters = [], $page = 1)
    {
        $cacheKey = "tmdb_discover_page_{$page}_" . md5(json_encode($filters));

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($filters, $page) {
            try {
                $params = [
                    'api_key' => $this->apiKey,
                    'language' => 'pt-BR',
                    'page' => $page,
                    'sort_by' => $filters['sort_by'] ?? 'popularity.desc',
                ];

                // Filtro por gênero
                if (!empty($filters['genre'])) {
                    $params['with_genres'] = $filters['genre'];
                }

                // Filtro por ano
                if (!empty($filters['year'])) {
                    $params['primary_release_year'] = $filters['year'];
                }

                // Filtro por nota mínima
                if (!empty($filters['vote_average_gte'])) {
                    $params['vote_average.gte'] = $filters['vote_average_gte'];
                }

                // Filtro por nota máxima
                if (!empty($filters['vote_average_lte'])) {
                    $params['vote_average.lte'] = $filters['vote_average_lte'];
                }

                $response = Http::get("{$this->baseUrl}/discover/movie", $params);

                if ($response->successful()) {
                    return $response->json();
                }

                return null;
            } catch (\Exception $e) {
                Log::error('Erro ao descobrir filmes: ' . $e->getMessage());
                return null;
            }
        });
    }
}


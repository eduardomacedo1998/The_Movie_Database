<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Services\TmdbService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class MovieController extends Controller
{
    protected $tmdbService;

    public function __construct(TmdbService $tmdbService)
    {
        $this->tmdbService = $tmdbService;
    }

    /**
     * Página inicial - exibe filmes populares ou filtrados
     */
    public function index(Request $request)
    {
        $genres = $this->tmdbService->getGenres();
        $page = $request->input('page', 1);

        // Verificar se há filtros aplicados
        $hasFilters = $request->hasAny(['genre', 'year', 'vote_average_gte', 'sort_by']);

        if ($hasFilters) {// Usar discover com filtros
            $filters = [
                'genre' => $request->input('genre'), // Gênero
                'year' => $request->input('year'), // Ano de lançamento
                'vote_average_gte' => $request->input('vote_average_gte'), // Nota mínima
                'sort_by' => $request->input('sort_by', 'popularity.desc'), // Ordenação
            ];
            $movies = $this->tmdbService->discoverMovies($filters, $page);
        } else {
            $movies = $this->tmdbService->getPopularMovies($page);
        }

        return view('movies.home', [
            'movies' => $movies['results'] ?? [],
            'genres' => $genres,
            'totalPages' => $movies['total_pages'] ?? 1,
            'currentPage' => $page,
            'filters' => $request->only(['genre', 'year', 'vote_average_gte', 'sort_by']),
        ]);
    }

    /**
     * Busca filmes por nome ou com filtros
     */
    public function search(Request $request)
    {
        $query = $request->input('q');
        $page = $request->input('page', 1);
        $genres = $this->tmdbService->getGenres();

        // Se não há query, mas há filtros, usar discover
        if (empty($query) && $request->hasAny(['genre', 'year', 'vote_average_gte', 'sort_by'])) {
            $filters = [
                'genre' => $request->input('genre'),
                'year' => $request->input('year'),
                'vote_average_gte' => $request->input('vote_average_gte'),
                'sort_by' => $request->input('sort_by', 'popularity.desc'),
            ];
            $searchResults = $this->tmdbService->discoverMovies($filters, $page);
            
            return view('movies.search', [
                'movies' => $searchResults['results'] ?? [],
                'query' => '',
                'totalResults' => $searchResults['total_results'] ?? 0,
                'currentPage' => $page,
                'totalPages' => $searchResults['total_pages'] ?? 1,
                'genres' => $genres,
                'filters' => $request->only(['genre', 'year', 'vote_average_gte', 'sort_by']),
            ]);
        }

        if (empty($query)) {
            return redirect()->route('home');
        }

        $searchResults = $this->tmdbService->searchMovies($query, $page);

        return view('movies.search', [
            'movies' => $searchResults['results'] ?? [],
            'query' => $query,
            'totalResults' => $searchResults['total_results'] ?? 0,
            'currentPage' => $page,
            'totalPages' => $searchResults['total_pages'] ?? 1,
            'genres' => $genres,
            'filters' => [],
        ]);
    }

    /**
     * Exibe lista de filmes favoritos do usuário
     */
    public function favorites(Request $request)
    {
        $userId = Auth::id();
        $genreFilter = $request->get('genre');

        // Cache da query de favoritos por 5 minutos
        $cacheKey = "user_favorites_{$userId}_genre_" . ($genreFilter ?: 'all');

        $favorites = Cache::remember($cacheKey, 300, function () use ($userId, $genreFilter) {
            $query = Favorite::where('user_id', $userId)
                ->with('user') // Eager loading
                ->orderBy('created_at', 'desc');

            if ($genreFilter) {
                $query->whereJsonContains('genres', $genreFilter);
            }

            return $query->get();
        });

        // Cache dos gêneros únicos por 1 hora
        $genresCacheKey = "user_genres_{$userId}";
        $allGenres = Cache::remember($genresCacheKey, 3600, function () use ($userId) {
            return Favorite::where('user_id', $userId)
                ->get()
                ->pluck('genres')
                ->flatten()
                ->unique()
                ->sort()
                ->values();
        });

        // Se for requisição AJAX, retorna JSON para uso no script
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'favorites' => $favorites->map(function($fav) {
                    return [
                        'id' => $fav->id,
                        'tmdb_id' => $fav->tmdb_id,
                    ];
                })
            ]);
        }

        return view('movies.favorites', [
            'favorites' => $favorites,
            'genres' => $allGenres,
            'selectedGenre' => $genreFilter,
        ]);
    }

    /**
     * Adiciona um filme aos favoritos
     */
    public function addFavorite($tmdbId)
    {
        $userId = Auth::id();

        // Verifica se já está nos favoritos
        if (Favorite::isFavorite($userId, $tmdbId)) {
            return response()->json([
                'success' => false,
                'message' => 'Este filme já está nos seus favoritos!'
            ], 400);
        }

        // Busca detalhes do filme na API
        $movieData = $this->tmdbService->getMovieDetails($tmdbId);

        if (!$movieData) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar informações do filme.'
            ], 500);
        }

        // Adiciona aos favoritos
        $favorite = Favorite::addFavorite($userId, $movieData);

        if ($favorite) {
            // Limpar cache dos favoritos do usuário
            Cache::forget("user_favorites_{$userId}_genre_all");
            Cache::forget("user_genres_{$userId}");

            return response()->json([
                'success' => true,
                'message' => 'Filme adicionado aos favoritos!',
                'favorite_id' => $favorite->id
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Erro ao adicionar filme aos favoritos.'
        ], 500);
    }

    /**
     * Remove um filme dos favoritos
     */
    public function removeFavorite($id)
    {
        $favorite = Favorite::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$favorite) {
            return response()->json([
                'success' => false,
                'message' => 'Filme não encontrado nos favoritos.'
            ], 404);
        }

        $favorite->delete();

        // Limpar cache dos favoritos do usuário
        $userId = Auth::id();
        Cache::forget("user_favorites_{$userId}_genre_all");
        Cache::forget("user_genres_{$userId}");

        return response()->json([
            'success' => true,
            'message' => 'Filme removido dos favoritos!'
        ]);
    }
}

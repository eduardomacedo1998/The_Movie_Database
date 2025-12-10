<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'tmdb_id',
        'title',
        'poster_path',
        'release_date',
        'genres',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'genres' => 'array',
        'release_date' => 'date',
    ];

    /**
     * Relacionamento com o usuário
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Verifica se um filme já está nos favoritos do usuário
     */
    public static function isFavorite($userId, $tmdbId)
    {
        return self::where('user_id', $userId)
            ->where('tmdb_id', $tmdbId)
            ->exists();
    }

    /**
     * Adiciona um filme aos favoritos
     */
    public static function addFavorite($userId, $movieData)
    {
        return self::create([
            'user_id' => $userId,
            'tmdb_id' => $movieData['id'],
            'title' => $movieData['title'],
            'poster_path' => $movieData['poster_path'] ?? null,
            'release_date' => $movieData['release_date'] ?? null,
            'genres' => isset($movieData['genres']) ? 
                        array_column($movieData['genres'], 'name') : [],
        ]);
    }
}

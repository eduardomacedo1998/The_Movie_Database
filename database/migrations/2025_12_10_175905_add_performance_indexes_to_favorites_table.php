<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('favorites', function (Blueprint $table) {
            // Índices de performance para melhorar queries
            $table->index(['user_id', 'tmdb_id'], 'idx_user_tmdb'); // Para verificação de favoritos
            $table->index('user_id', 'idx_user_id'); // Para listagem de favoritos por usuário
            $table->index('tmdb_id', 'idx_tmdb_id'); // Para buscas por ID TMDB
            $table->index('release_date', 'idx_release_date'); // Para filtros por data
            $table->index('created_at', 'idx_created_at'); // Para ordenação por data de criação
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('favorites', function (Blueprint $table) {
            // Remover índices de performance
            $table->dropIndex('idx_user_tmdb');
            $table->dropIndex('idx_user_id');
            $table->dropIndex('idx_tmdb_id');
            $table->dropIndex('idx_release_date');
            $table->dropIndex('idx_created_at');
        });
    }
};

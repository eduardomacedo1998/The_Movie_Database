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
        Schema::create('favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('tmdb_id')->index();
            $table->string('title');
            $table->string('poster_path')->nullable();
            $table->date('release_date')->nullable();
            $table->json('genres')->nullable();
            $table->timestamps();

            // Garantir que um usuário não adicione o mesmo filme duas vezes
            $table->unique(['user_id', 'tmdb_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('favorites');
    }
};

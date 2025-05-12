<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('aulas', function (Blueprint $table) {
            $table->id();
            $table->integer('sequencia');
            $table->string('titulo');
            $table->integer('duracaoMinutos');
            $table->string('videoUrl')->default(null);
            $table->foreignId('curso_id')->constrained('cursos')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['curso_id', 'sequencia']);
        });
        /**
         *  id: { type: Number, default: null },
            sequencia: Number,
            titulo: String,
            duracaoMinutos: Number,
            vista: Boolean,
            curso_id: Number,
            video: String,
         */
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aulas');
    }
};

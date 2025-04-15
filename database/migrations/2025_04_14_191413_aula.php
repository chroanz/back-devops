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
        Schema::create('cursos', function (Blueprint $table) {
            $table->id();
            $table->integer('sequencia');
            $table->string('titulo');
            $table->integer('duracaoMinutos');
            $table->string('videoUrl');
            $table->boolean('vista')->default(false);
            $table->string('video')->default(null);
            $table->foreignId('curso_id')->constrained('cursos')->onDelete('cascade');
            $table->timestamps();
        });
        /**
         *  id: { type: Number, default: null },
            sequencia: Number,
            titulo: String,
            duracaoMinutos: Number,
            videoUrl: String,
            vista: Boolean,
            curso_id: Number,
            video: { type: String, default: null },
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

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class aulas extends Model
{
    /** @use HasFactory<\Database\Factories\AulasFactory> */
    use HasFactory;

    protected $fillable = [
        'sequencia',
        'titulo',
        'duracaoMinutos',
        'videoUrl',
        'vista',
        'video',
        'curso_id',
    ];
}

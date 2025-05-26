<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Aulas extends Model
{
    /** @use HasFactory<\Database\Factories\AulasFactory> */
    use HasFactory;

    protected $fillable = [
        'sequencia',
        'titulo',
        'duracaoMinutos',
        'videoUrl',
        'curso_id',
    ];

    protected $hidden = [
        'videoUrl'
    ];

    public function curso(): BelongsTo{
        return $this->belongsTo(Cursos::class, 'curso_id');
    }

    public function users(): BelongsToMany{
        return $this->belongsToMany(User::class, 'aulas_user', 'aula_id', 'user_id')->withTimestamps();
    }
}

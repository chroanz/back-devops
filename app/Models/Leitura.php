<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Leitura extends Model
{
    /** @use HasFactory<\Database\Factories\LeituraFactory> */
    use HasFactory;

    protected $fillable = [
        'curso_id',
        'sequencia',
        'titulo',
        'conteudo',
    ];

    protected $hidden = [
        'conteudo'
    ];

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Cursos::class, 'curso_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cursos extends Model
{
    protected $table = 'cursos';

    protected $fillable = [
        'titulo',
        'descricao',
        'categoria',
        'capa'
    ];

    public $timestamps = true;

    public static function getByParam($param)
    {
        return self::where('titulo', 'LIKE', "%{$param}%")
            ->orWhere('descricao', 'LIKE', "%{$param}%")
            ->orWhere('categoria', 'LIKE', "%{$param}%")
            ->get();
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'cursos_user', 'cursos_id', 'user_id')->withTimestamps();
    }

    public function aulas(): HasMany
    {
        return $this->hasMany(Aulas::class, 'curso_id');
    }

    public function leituras(): HasMany
    {
        return $this->hasMany(Leitura::class, 'curso_id');
    }
}

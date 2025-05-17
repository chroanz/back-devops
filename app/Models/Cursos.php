<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Cursos extends Model
{
    use HasFactory;

    protected $table = 'cursos';

    protected $fillable = [
        'titulo',
        'descricao',
        'categoria',
        'capa',
        'capa_url',
        'capa_expiration'
    ];

    protected $appends = ['capa_url'];

    protected $hidden = ['capa', 'capa_expiration'];

    public $timestamps = true;

    public static function getByParam($param)
    {
        return self::where('titulo', 'LIKE', "%{$param}%")
            ->orWhere('descricao', 'LIKE', "%{$param}%")
            ->orWhere('categoria', 'LIKE', "%{$param}%")
            ->get();
    }

    public function getCapaUrlAttribute()
    {
        if (empty($this->capa)) {
            return null;
        }

        // Se já existe uma URL temporária válida, retorna ela
        if (
            !empty($this->attributes['capa_url']) &&
            !empty($this->attributes['capa_expiration']) &&
            now()->lt($this->attributes['capa_expiration'])
        ) {
            return $this->attributes['capa_url'];
        }

        // Gera nova URL temporária
        $url = Storage::disk('s3')->temporaryUrl($this->capa, now()->addDays(7));

        // Atualiza no banco
        $this->update([
            'capa_url' => $url,
            'capa_expiration' => now()->addDays(7)
        ]);

        return $url;
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

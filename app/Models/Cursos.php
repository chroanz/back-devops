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
        $param = strtolower($param);
        return self::whereLike('titulo', "%{$param}%")
            ->orWhereLike('descricao', "%{$param}%")
            ->orWhereLike('categoria',  "%{$param}%")
            ->with(['leituras', 'aulas'])->get();
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

        try{
            // Gera nova URL temporária
            $url = Storage::disk('s3')->temporaryUrl($this->capa, now()->addDays(7));

            // Atualiza no banco, ignorando percentual de conclusão, que não deve ser persistido no banco
            $percentual = $this->percentual_conclusao ?? null;
            unset($this->percentual_conclusao);
            $this->update([
                'capa_url' => $url,
                'capa_expiration' => now()->addDays(7)
            ]);
            if($percentual){
                $this->percentual_conclusao = $percentual;
            }}
        catch (\Throwable $e) {
            // Se falhar, retorna a URL original
            return $this->attributes['capa_url'] ?? null;
        }

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

    public function calcularPercentualConclusao(User $user): int
    {
        $totalAulas = $this->aulas->count();
        $totalLeituras = $this->leituras->count();
        $total = $totalAulas + $totalLeituras;

        $aulasVistas = $this->aulas->filter(function ($aula) use ($user) {
            return $aula->users()->where('user_id', $user->id)->exists();
        })->count();

        $leiturasVistas = $this->leituras->filter(function ($leitura) use ($user) {
            return $leitura->users()->where('user_id', $user->id)->exists();
        })->count();

        $totalVistos = $aulasVistas + $leiturasVistas;

        return $total > 0 ? round(($totalVistos / $total) * 100) : 0;
    }
}

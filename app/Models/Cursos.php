<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Cursos extends Model
{
    protected $table = 'cursos';

    protected $fillable = [
        'titulo',
        'descricao',
        'categoria',
    ];

    public $timestamps = true;

    public static function getByParam($param)
    {
        return self::where('titulo', 'LIKE', "%{$param}%")
            ->orWhere('descricao', 'LIKE', "%{$param}%")
            ->orWhere('categoria', 'LIKE', "%{$param}%")
            ->get();
    }

    public function users() :BelongsToMany{
        return $this->belongsToMany(User::class);
    }
}

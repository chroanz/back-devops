<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}

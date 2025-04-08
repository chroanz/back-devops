<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserFunction extends Model
{
    protected $table = 'user_functions';

    protected $fillable = [
        'user_id',
        'function',
    ];
    

    public function byUserId($userId)
    {
        return self::where('user_id', $userId)->get();
    }

    
}

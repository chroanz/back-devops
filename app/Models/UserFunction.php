<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserFunction extends Model
{
    use HasFactory;

    protected $table = 'user_functions';

    protected $fillable = [
        'user_id',
        'function',
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function byUserId($userId)
    {
        return self::where('user_id', $userId)->get();
    }

    
}

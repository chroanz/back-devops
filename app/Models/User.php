<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Mail\ResetPassword;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function functions()
    {
        return $this->hasMany(UserFunction::class);
    }

    public function isAdmin()
    {
        return $this->functions()->where('function', 'admin')->exists();
    }

    public function isDefault()
    {
        return $this->functions()->where('function', 'default')->exists();
    }

    public function cursos(): BelongsToMany{
        return $this->belongsToMany(Cursos::class, 'cursos_user', 'user_id', 'cursos_id')->withTimestamps();
    }

    public function sendPasswordResetNotification($token): void
    {
        $url = env('FRONTEND_URL') . '/recuperar-senha?token=' . $token . '&email=' . $this->email;
        $this->notify(new ResetPasswordNotification($url));
    }

}

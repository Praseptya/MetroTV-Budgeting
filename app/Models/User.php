<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'id_user';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false; // Disable timestamps

    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
        'user_level_id',
    ];

    protected $hidden = [
        'password',
    ];

    public function userLevel()
    {
        return $this->belongsTo(UserLevel::class, 'user_level_id', 'id_level');
    }

    public function budgets()
    {
        return $this->hasMany(Budget::class, 'created_by', 'id_user');
    }

    public function templates()
    {
        return $this->hasMany(Template::class, 'created_by', 'id_user');
    }
}
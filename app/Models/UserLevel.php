<?php

// UserLevel.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLevel extends Model
{
    use HasFactory;

    protected $table = 'user_levels';
    protected $primaryKey = 'id_level';
    public $timestamps = false;

    protected $fillable = [
        'level_name',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'user_level_id', 'id_level');
    }

    public function accessRights()
    {
        return $this->hasMany(UserLevelAccess::class, 'user_level_id', 'id_level');
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLevelAccess extends Model
{
    use HasFactory;

    protected $table = 'user_level_access';
    protected $primaryKey = 'id_access';
    public $timestamps = false;

    protected $fillable = [
        'user_level_id',
        'feature_name',
        'access',
    ];

    protected $casts = [
        'access' => 'boolean',
    ];

    public function userLevel()
    {
        return $this->belongsTo(UserLevel::class, 'user_level_id', 'id_level');
    }
}
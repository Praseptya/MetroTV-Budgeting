<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Program extends Model
{
    protected $table = 'programs';
    protected $primaryKey = 'id_program';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'name', 'category', 'pic_user_id', 'description',
    ];

    public function pic(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'pic_user_id', 'id_user');
    }

    /* accessor untuk tampilan kapital di tabel */
    public function getNameCapAttribute(): string
    {
        return ucwords(mb_strtolower($this->name ?? ''));
    }

    public function getDescCapAttribute(): string
    {
        return $this->description ? ucfirst($this->description) : '';
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Template extends Model
{
    protected $table = 'templates';
    protected $primaryKey = 'id_template'; // PK custom
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'name', 'id_event_program', 'pic_user_id', 'category', 'description'
    ];

    public function items(): HasMany {
        return $this->hasMany(TemplateItem::class, 'template_id', 'id_template');
    }

    public function program(): BelongsTo {
        // Model EventProgram kamu (buat jika belum ada)
        return $this->belongsTo(\App\Models\EventProgram::class, 'id_event_program', 'id_event_program');
    }

    public function pic(): BelongsTo {
        // Users kamu PK = id_user
        return $this->belongsTo(\App\Models\User::class, 'pic_user_id', 'id_user');
    }
}

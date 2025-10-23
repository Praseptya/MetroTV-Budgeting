<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventProgram extends Model
{
    use HasFactory;

    protected $table = 'event_programs';
    protected $primaryKey = 'id_program';
    public $timestamps = false;

    protected $fillable = [
        'name',
        'type',
        'description',
    ];

    public function templates()
    {
        return $this->hasMany(Template::class, 'program_id', 'id_program');
    }
}
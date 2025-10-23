<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TemplateItem extends Model
{
    protected $table = 'template_items';
    protected $primaryKey = 'id_template_item';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'template_id','item_id','qty',
        'item_name','unit','unit_price','short_desc'
    ];

    public function template(): BelongsTo {
        return $this->belongsTo(Template::class, 'template_id', 'id_template');
    }
}

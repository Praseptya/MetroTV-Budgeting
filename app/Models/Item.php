<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $table = 'items';
    protected $primaryKey = 'id_item';
    public $timestamps = false;

    protected $fillable = [
        'item_name',
        'bottom_price',
        'top_price',
        'unit',
        'margin',
        'me',
    ];

    protected $casts = [
        'bottom_price' => 'decimal:2',
        'top_price' => 'decimal:2',
        'margin' => 'decimal:2',
    ];

    public function budgetItems()
    {
        return $this->hasMany(BudgetItem::class, 'item_id', 'id_item');
    }

    public function templateItems()
    {
        return $this->hasMany(TemplateItem::class, 'item_id', 'id_item');
    }
}
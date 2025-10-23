<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BudgetItem extends Model
{
    use HasFactory;

    protected $table = 'budget_items';
    protected $primaryKey = 'id_budget_item';
    public $timestamps = false;

    protected $fillable = [
        'budget_id',
        'item_id',
        'qty',
        'unit',
        'top_price',
        'bottom_price',
        'amount',
    ];

    protected $casts = [
        'top_price' => 'decimal:2',
        'bottom_price' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    public function budget()
    {
        return $this->belongsTo(Budget::class, 'budget_id', 'id_budget');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id', 'id_item');
    }
}
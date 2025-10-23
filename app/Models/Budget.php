<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    use HasFactory;

    protected $table = 'budgets';
    protected $primaryKey = 'id_budget';
    public $incrementing = true;
    protected $keyType = 'int';
    
    // Enable timestamps since you have created_at and updated_at in your table
    public $timestamps = true; 
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $dates = ['created_at', 'updated_at'];
    protected $fillable = [
        'master_name',
        'description',
        'periode_from',
        'periode_to',
        'pic',
        'dept',
        'template_id',
        'created_by',
    ];

    protected $casts = [
        'periode_from' => 'date',
        'periode_to' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function template()
    {
        return $this->belongsTo(Template::class, 'template_id', 'id_template');
    }

    public function items()
    {
        // tabel detail pengajuan budget
        return $this->hasMany(BudgetItem::class, 'budget_id', $this->getKeyName());
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id_user');
    }

    public function budgetItems()
    {
        return $this->hasMany(BudgetItem::class, 'budget_id', 'id_budget');
    }

    public function approvals()
    {
        return $this->hasMany(BudgetApproval::class, 'budget_id', 'id_budget')
                    ->orderByDesc('created_at');
    }

    // Accessor untuk mendapatkan status approval terbaru
    public function getLatestApprovalStatusAttribute()
    {
        $latestApproval = $this->approvals()->first();
        return $latestApproval ? $latestApproval->status : 'Pending';
    }

    // Accessor untuk mendapatkan total budget
    public function getTotalBudgetAttribute()
    {
        return $this->budgetItems()->sum('amount') ?? 0;
    }
}
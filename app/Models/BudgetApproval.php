<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BudgetApproval extends Model
{
    protected $table = 'budget_approvals';
    protected $primaryKey = 'id_approval';
    
    protected $fillable = [
        'budget_id',
        'approved_by', 
        'status',
        'comment',
        'approved_at'
    ];

    protected $casts = [
        'approved_at' => 'datetime'
    ];

    public function budget()
    {
        return $this->belongsTo(Budget::class, 'budget_id', 'id_budget');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by', 'id_user');
    }
}
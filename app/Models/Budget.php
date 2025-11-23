<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Category;

class Budget extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'category_id', 'amount', 'period', 'start_date', 'end_date'];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'budget_user')->withPivot('role')->withTimestamps();
    }

    public function expenseRequests()
    {
        return $this->hasMany(ExpenseRequest::class);
    }
}

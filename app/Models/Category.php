<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User; // Added for User relationship
use App\Models\Transaction; // Added for Transaction relationship
use App\Models\Budget; // Added for Budget relationship

class Category extends Model
{
    protected $fillable = ['user_id', 'name', 'type', 'color'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function budgets()
    {
        return $this->hasMany(Budget::class);
    }
}

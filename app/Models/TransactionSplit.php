<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionSplit extends Model
{
    protected $fillable = ['transaction_id', 'category_id', 'amount', 'description'];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}

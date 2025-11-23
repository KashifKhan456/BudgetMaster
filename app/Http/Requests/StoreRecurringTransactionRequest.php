<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRecurringTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => 'nullable|exists:categories,id',
            'amount' => 'required|numeric|min:0',
            'type' => 'required|in:expense,income',
            'interval' => 'required|in:daily,weekly,monthly,yearly',
            'start_date' => 'required|date',
            'description' => 'nullable|string|max:255',
        ];
    }
}

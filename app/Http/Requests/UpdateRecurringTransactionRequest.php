<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateRecurringTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->recurring->user_id === Auth::id();
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

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->transaction->user_id === Auth::id();
    }

    public function rules(): array
    {
        return [
            'category_id' => 'nullable|exists:categories,id',
            'amount' => 'required|numeric|min:0',
            'type' => 'required|in:expense,income',
            'date' => 'required|date',
            'description' => 'nullable|string|max:255',
        ];
    }
}

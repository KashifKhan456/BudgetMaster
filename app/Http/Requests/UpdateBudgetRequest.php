<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateBudgetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->budget->user_id === Auth::id();
    }

    public function rules(): array
    {
        return [
            'category_id' => 'required|exists:categories,id',
            'amount' => 'required|numeric|min:0',
            'period' => 'required|in:month,year',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ];
    }
}

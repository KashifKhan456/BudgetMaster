<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateSavingsGoalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->goal->user_id === Auth::id();
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'target_amount' => 'required|numeric|min:0',
            'target_date' => 'required|date',
        ];
    }
}

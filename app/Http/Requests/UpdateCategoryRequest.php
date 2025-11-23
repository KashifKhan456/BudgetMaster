<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->category->user_id === Auth::id();
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'type' => 'required|in:expense,income',
            'color' => 'nullable|string|max:20',
        ];
    }
}

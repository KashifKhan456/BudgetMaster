<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class AddFundsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->goal->user_id === Auth::id();
    }

    public function rules(): array
    {
        return [
            'add_amount' => 'required|numeric|min:0',
        ];
    }
}

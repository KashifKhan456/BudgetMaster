<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
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
            'date' => 'required|date',
            'description' => 'nullable|string|max:255',
            'splits' => 'nullable|array',
            'splits.*.category_id' => 'required|exists:categories,id',
            'splits.*.amount' => 'required|numeric|min:0',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $splits = $this->input('splits');
            if (!empty($splits)) {
                $totalAmount = $this->input('amount');
                $splitSum = array_reduce($splits, function ($carry, $split) {
                    return $carry + $split['amount'];
                }, 0);

                if (abs($totalAmount - $splitSum) > 0.01) {
                    $validator->errors()->add('amount', 'The sum of splits must equal the total amount.');
                }
            }
        });
    }
}

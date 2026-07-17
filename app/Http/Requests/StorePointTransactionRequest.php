<?php

namespace App\Http\Requests;

use App\Models\PointTransaction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePointTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'points' => ['required', 'integer', 'min:1', 'max:100000000'],
            'action_type' => ['required', Rule::in([PointTransaction::EARN, PointTransaction::DEDUCT])],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }
}

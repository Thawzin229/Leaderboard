<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LeaderboardRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'filter' => ['nullable', Rule::in(['today', 'week', 'month', 'all'])],
        ];
    }

    public function filter(): string
    {
        return $this->validated('filter', 'all');
    }
}

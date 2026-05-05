<?php

namespace App\Http\Requests\Share;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ShareDocumentRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->filled('user_id') && !$this->has('user_ids')) {
            $this->merge([
                'user_ids' => [$this->input('user_id')],
            ]);
        }
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['required', 'integer', 'distinct', 'exists:users,id'],
            'role' => ['required', Rule::in(['viewer', 'editor'])],
        ];
    }
}

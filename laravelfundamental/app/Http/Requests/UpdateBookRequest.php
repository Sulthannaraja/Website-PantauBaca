<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'ADMIN';
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'author' => ['sometimes', 'required', 'string', 'max:150'],
            'category' => ['nullable', 'string', 'max:100'],
            'cover' => ['nullable', 'string', 'max:500'],
            'file' => ['nullable', 'file', 'mimetypes:application/pdf', 'max:20480'],
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:150'],
            'category' => ['nullable', 'string', 'max:100'],
            'cover' => ['nullable', 'string', 'max:500'],
            'file' => ['required', 'file', 'mimetypes:application/pdf', 'max:20480'],
        ];
    }
}

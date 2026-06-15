<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreQuizRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->role, ['GURU', 'ADMIN'], true);
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'bookId' => ['required', 'integer', 'exists:books,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'questions' => ['required', 'array', 'min:1'],
            'questions.*.type' => ['required', Rule::in(['MCQ', 'ESSAY'])],
            'questions.*.question' => ['required', 'string'],
            'questions.*.options' => ['nullable', 'array'],
            'questions.*.options.*' => ['string'],
            'questions.*.answer' => ['nullable', 'string'],
            'questions.*.points' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function withValidator($validator): void
    {
        $questions = $this->input('questions', []);

        foreach ($questions as $index => $question) {
            if (isset($question['type']) && $question['type'] === 'MCQ') {
                $validator->sometimes("questions.{$index}.options", ['required', 'array', 'min:2'], fn () => true);
                $validator->sometimes("questions.{$index}.answer", ['required', 'string'], fn () => true);
            }
        }
    }

    protected function prepareForValidation(): void
    {
        $questions = $this->input('questions', []);

        if (is_array($questions)) {
            $this->merge(['questions' => array_values($questions)]);
        }
    }
}

<?php

namespace App\Http\Requests\Comment;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'content' => ['required', 'string', 'min:2'],
            'media_images' => ['nullable', 'array', 'max:5'],
            'media_images.*' => ['image', 'max:4096'],
        ];
    }
}


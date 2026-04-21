<?php

namespace App\Http\Requests\Post;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:3', 'max:255'],
            'content' => ['required', 'string', 'min:10'],
            'thumbnail' => ['nullable', 'string', 'max:2048'],
            'thumbnail_file' => ['nullable', 'image', 'max:4096'],
            'media_images' => ['nullable', 'array', 'max:5'],
            'media_images.*' => ['image', 'max:4096'],
        ];
    }
}

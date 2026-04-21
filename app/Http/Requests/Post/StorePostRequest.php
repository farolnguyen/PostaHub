<?php

namespace App\Http\Requests\Post;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:3', 'max:255'],
            'url' => ['required', 'string', 'min:3', 'max:255', 'alpha_dash', 'unique:posts,url'],
            'content' => ['required', 'string', 'min:10'],
            'thumbnail' => ['nullable', 'string', 'max:2048'],
            'thumbnail_file' => ['nullable', 'image', 'max:4096'],
            'media_images' => ['nullable', 'array'],
            'media_images.*' => ['image', 'max:4096'],
        ];
    }
}

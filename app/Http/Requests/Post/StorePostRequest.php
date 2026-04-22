<?php

namespace App\Http\Requests\Post;

use App\Support\UploadErrorLogger;
use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        UploadErrorLogger::logFromPhpFiles(['media_images', 'thumbnail_file'], self::class);
    }

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
            'media_images.*' => ['file', 'mimetypes:image/jpeg,image/png,image/gif,image/webp,image/bmp,image/svg+xml,video/mp4,video/webm,video/ogg,audio/mpeg,audio/mp3,audio/wav,audio/ogg,audio/webm,audio/mp4,audio/x-m4a', 'max:102400'],
        ];
    }
}

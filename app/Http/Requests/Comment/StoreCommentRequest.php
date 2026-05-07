<?php

namespace App\Http\Requests\Comment;

use App\Support\UploadErrorLogger;
use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        UploadErrorLogger::logFromPhpFiles(['media_images'], self::class);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'content'        => ['nullable', 'string', 'min:2', 'required_without:media_images'],
            'media_images'   => ['nullable', 'array', 'max:5'],
            'media_images.*' => ['file', 'mimetypes:image/jpeg,image/png,image/gif,image/webp,image/bmp,image/svg+xml,video/mp4,video/webm,video/ogg,audio/mpeg,audio/mp3,audio/wav,audio/ogg,audio/webm,audio/mp4,audio/x-m4a', 'max:102400'],
        ];
    }
}

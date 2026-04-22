<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;

class UploadErrorLogger
{
    /**
     * @param  list<string>  $fields
     */
    public static function logFromPhpFiles(array $fields, string $context): void
    {
        foreach ($fields as $field) {
            if (! isset($_FILES[$field])) {
                continue;
            }

            $errors = self::collectErrors($_FILES[$field]['error'] ?? null);
            if ($errors === []) {
                continue;
            }

            Log::warning('Upload failed before validation', [
                'context' => $context,
                'field' => $field,
                'errors' => $errors,
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'post_max_size' => ini_get('post_max_size'),
            ]);
        }
    }

    /**
     * @return list<array{code:int,message:string}>
     */
    private static function collectErrors(mixed $errorNode): array
    {
        if (is_int($errorNode)) {
            if ($errorNode === UPLOAD_ERR_OK || $errorNode === UPLOAD_ERR_NO_FILE) {
                return [];
            }

            return [[
                'code' => $errorNode,
                'message' => self::uploadErrorMessage($errorNode),
            ]];
        }

        if (! is_array($errorNode)) {
            return [];
        }

        $result = [];
        foreach ($errorNode as $child) {
            foreach (self::collectErrors($child) as $item) {
                $result[] = $item;
            }
        }

        return $result;
    }

    private static function uploadErrorMessage(int $code): string
    {
        return match ($code) {
            UPLOAD_ERR_INI_SIZE => 'File vuot upload_max_filesize (php.ini).',
            UPLOAD_ERR_FORM_SIZE => 'File vuot gioi han MAX_FILE_SIZE cua form.',
            UPLOAD_ERR_PARTIAL => 'File chi duoc upload mot phan.',
            UPLOAD_ERR_NO_TMP_DIR => 'Thieu thu muc tam de upload.',
            UPLOAD_ERR_CANT_WRITE => 'Khong the ghi file tam len disk.',
            UPLOAD_ERR_EXTENSION => 'Upload bi chan boi extension PHP.',
            default => 'Loi upload khong xac dinh.',
        };
    }
}


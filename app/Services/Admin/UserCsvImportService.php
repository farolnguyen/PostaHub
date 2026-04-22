<?php

namespace App\Services\Admin;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use OpenSpout\Reader\CSV\Reader;

final class UserCsvImportService
{
    /**
     * @return array{imported: int, errors: list<array{line: int, messages: list<string>}>}
     */
    public function importFromPath(string $absolutePath): array
    {
        $imported = 0;
        $errors = [];

        $reader = new Reader;
        $reader->open($absolutePath);

        try {
            $headerMap = null;
            $line = 0;
            $anyRow = false;

            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $row) {
                    $anyRow = true;
                    $line++;
                    $cells = $row->toArray();
                    $values = array_map(static function (mixed $v): string {
                        if ($v instanceof \DateTimeInterface) {
                            return $v->format('Y-m-d H:i:s');
                        }
                        if (is_bool($v)) {
                            return $v ? '1' : '0';
                        }
                        if (is_numeric($v)) {
                            return (string) $v;
                        }

                        return trim((string) $v);
                    }, $cells);

                    if ($headerMap === null) {
                        if ($values !== [] && isset($values[0])) {
                            $values[0] = preg_replace('/^\xEF\xBB\xBF/', '', $values[0]) ?? $values[0];
                        }
                        $headerMap = [];
                        foreach ($values as $i => $h) {
                            $key = strtolower(trim((string) $h));
                            if ($key !== '') {
                                $headerMap[$i] = $key;
                            }
                        }
                        if (! in_array('name', $headerMap, true) || ! in_array('email', $headerMap, true) || ! in_array('password', $headerMap, true)) {
                            $errors[] = ['line' => $line, 'messages' => ['Dòng tiêu đề phải có các cột: name, email, password (không phân biệt hoa thường).']];
                            $headerMap = false;
                        }

                        continue;
                    }

                    if ($headerMap === false) {
                        break 2;
                    }

                    if ($this->rowIsEmpty($values)) {
                        continue;
                    }

                    $assoc = [];
                    foreach ($headerMap as $index => $key) {
                        $assoc[$key] = $values[$index] ?? '';
                    }

                    $validator = Validator::make($assoc, [
                        'name' => ['required', 'string', 'min:3', 'max:255'],
                        'email' => ['required', 'email', 'max:255', 'unique:users,email'],
                        'password' => ['required', 'string', 'min:8', 'max:255'],
                        'can_post' => ['nullable', 'string', 'max:32'],
                        'can_comment' => ['nullable', 'string', 'max:32'],
                    ]);

                    if ($validator->fails()) {
                        $errors[] = [
                            'line' => $line,
                            'messages' => $validator->errors()->all(),
                        ];

                        continue;
                    }

                    $canPost = $this->parseOptionalBool($assoc['can_post'] ?? null, true);
                    $canComment = $this->parseOptionalBool($assoc['can_comment'] ?? null, true);

                    try {
                        DB::transaction(function () use ($assoc, $canPost, $canComment): void {
                            $user = User::create([
                                'name' => $assoc['name'],
                                'email' => $assoc['email'],
                                'password' => $assoc['password'],
                            ]);
                            $user->rule()->create([
                                'can_post' => $canPost,
                                'can_comment' => $canComment,
                            ]);
                        });
                        $imported++;
                    } catch (\Throwable $e) {
                        $errors[] = [
                            'line' => $line,
                            'messages' => ['Lỗi khi lưu: '.$e->getMessage()],
                        ];
                    }
                }
            }

            if (! $anyRow) {
                $errors[] = ['line' => 0, 'messages' => ['File CSV không có dòng dữ liệu.']];
            } elseif ($headerMap === null) {
                $errors[] = ['line' => 0, 'messages' => ['File CSV không có dòng tiêu đề hợp lệ.']];
            }
        } finally {
            $reader->close();
        }

        return compact('imported', 'errors');
    }

    /**
     * @param  list<string>  $values
     */
    private function rowIsEmpty(array $values): bool
    {
        foreach ($values as $v) {
            if ($v !== '') {
                return false;
            }
        }

        return true;
    }

    private function parseOptionalBool(?string $raw, bool $default): bool
    {
        if ($raw === null || $raw === '') {
            return $default;
        }

        $n = strtolower(trim($raw));

        return match ($n) {
            '0', 'false', 'no', 'off', 'không' => false,
            '1', 'true', 'yes', 'on', 'có' => true,
            default => $default,
        };
    }
}

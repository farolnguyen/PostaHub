<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Carbon;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    /**
     * @var list<string>
     */
    private const USER_HEADERS = ['id', 'name', 'email', 'email_verified_at', 'password', 'remember_token'];

    /**
     * @var list<string>
     */
    private const POST_HEADERS = ['id', 'user_id', 'title', 'url', 'content', 'thumbnail'];

    public function usersCsv(): StreamedResponse
    {
        $filename = 'users_'.now()->format('Y-m-d_His').'.csv';

        return response()->streamDownload(function (): void {
            $out = fopen('php://output', 'w');
            if ($out === false) {
                return;
            }
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, self::USER_HEADERS);
            User::query()->orderBy('id')->chunk(200, function ($users) use ($out): void {
                foreach ($users as $user) {
                    fputcsv($out, $this->userToCsvValues($user));
                }
            });
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function usersXlsx(): BinaryFileResponse
    {
        $path = $this->tempExportPath('users');

        $writer = new Writer;
        $writer->openToFile($path);
        $writer->addRow(Row::fromValues(self::USER_HEADERS));

        User::query()->orderBy('id')->chunk(200, function ($users) use ($writer): void {
            foreach ($users as $user) {
                $writer->addRow(Row::fromValues($this->userToScalarRow($user)));
            }
        });

        $writer->close();

        return response()->download($path, 'users_'.now()->format('Y-m-d_His').'.xlsx')
            ->deleteFileAfterSend(true);
    }

    public function postsCsv(): StreamedResponse
    {
        $filename = 'posts_'.now()->format('Y-m-d_His').'.csv';

        return response()->streamDownload(function (): void {
            $out = fopen('php://output', 'w');
            if ($out === false) {
                return;
            }
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, self::POST_HEADERS);
            Post::query()->orderBy('id')->chunk(100, function ($posts) use ($out): void {
                foreach ($posts as $post) {
                    fputcsv($out, $this->postToCsvValues($post));
                }
            });
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function postsXlsx(): BinaryFileResponse
    {
        $path = $this->tempExportPath('posts');

        $writer = new Writer;
        $writer->openToFile($path);
        $writer->addRow(Row::fromValues(self::POST_HEADERS));

        Post::query()->orderBy('id')->chunk(100, function ($posts) use ($writer): void {
            foreach ($posts as $post) {
                $writer->addRow(Row::fromValues($this->postToScalarRow($post)));
            }
        });

        $writer->close();

        return response()->download($path, 'posts_'.now()->format('Y-m-d_His').'.xlsx')
            ->deleteFileAfterSend(true);
    }

    private function tempExportPath(string $prefix): string
    {
        $tmp = tempnam(sys_get_temp_dir(), $prefix.'_xlsx_');
        if ($tmp === false) {
            abort(500, 'Không tạo được file tạm.');
        }

        return $tmp;
    }

    /**
     * @return list<string|int|null>
     */
    private function userToCsvValues(User $user): array
    {
        return [
            (string) $user->id,
            $user->name,
            $user->email,
            $this->formatDateTime($user->email_verified_at),
            $user->password,
            $user->remember_token ?? '',
        ];
    }

    /**
     * @return list<string|int|null>
     */
    private function userToScalarRow(User $user): array
    {
        return $this->userToCsvValues($user);
    }

    /**
     * @return list<string|int|null>
     */
    private function postToCsvValues(Post $post): array
    {
        $fullUrl = route('post.detail', ['url' => $post->url]);

        return [
            (string) $post->id,
            (string) $post->user_id,
            $post->title,
            $fullUrl,
            $this->cleanHtmlContent($post->content),
            $post->thumbnail ?? '',
        ];
    }

    /**
     * @return list<string|int|null>
     */
    private function postToScalarRow(Post $post): array
    {
        return $this->postToCsvValues($post);
    }

    private function formatDateTime(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }
        if ($value instanceof Carbon) {
            return $value->format('Y-m-d H:i:s');
        }

        return (string) $value;
    }

    private function cleanHtmlContent(string $html): string
    {
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

        return trim($text);
    }
}

<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder dữ liệu dummy lớn: mặc định 1000 user, mỗi user 100 bài post (100.000 post).
 *
 * Chạy riêng (không gọi tự động từ DatabaseSeeder để tránh làm chậm migrate:fresh thường ngày):
 *   php artisan db:seed --class=DummyBulkSeeder
 *
 * Ghi đè số lượng (ví dụ test nhanh):
 *   DUMMY_SEED_USERS=50 DUMMY_SEED_POSTS_PER_USER=10 php artisan db:seed --class=DummyBulkSeeder
 */
class DummyBulkSeeder extends Seeder
{
    private const EMAIL_PREFIX = 'bulkdummy-seed-';

    private const EMAIL_DOMAIN = 'seed.postahub.local';

    public function run(): void
    {
        $userCount = max(1, (int) env('DUMMY_SEED_USERS', 1000));
        $postsPerUser = max(1, (int) env('DUMMY_SEED_POSTS_PER_USER', 100));

        $this->command?->info("DummyBulkSeeder: {$userCount} user(s), {$postsPerUser} post/user...");

        DB::disableQueryLog();

        $this->deletePreviousBulkSeed();

        $now = now();
        $password = Hash::make('password');

        $userChunkSize = 200;
        $postInsertChunk = 1000;

        for ($offset = 0; $offset < $userCount; $offset += $userChunkSize) {
            $take = min($userChunkSize, $userCount - $offset);
            $userRows = [];
            for ($i = 0; $i < $take; $i++) {
                $n = $offset + $i;
                $userRows[] = [
                    'name' => 'Dummy User '.$n,
                    'email' => self::EMAIL_PREFIX.str_pad((string) $n, 6, '0', STR_PAD_LEFT).'@'.self::EMAIL_DOMAIN,
                    'email_verified_at' => $now,
                    'password' => $password,
                    'remember_token' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            DB::table('users')->insert($userRows);
        }

        $userIds = DB::table('users')
            ->where('email', 'like', self::EMAIL_PREFIX.'%@'.self::EMAIL_DOMAIN)
            ->orderBy('email')
            ->pluck('id')
            ->all();

        if (count($userIds) !== $userCount) {
            $this->command?->error('Số user sau insert không khớp '.$userCount.' (thực tế '.count($userIds).'). Kiểm tra DB/constraint.');

            return;
        }

        $ruleRows = [];
        foreach ($userIds as $userId) {
            $ruleRows[] = [
                'user_id' => $userId,
                'can_post' => true,
                'can_comment' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($ruleRows, 500) as $chunk) {
            DB::table('user_rules')->insert($chunk);
        }

        $postRows = [];
        foreach ($userIds as $userId) {
            for ($p = 1; $p <= $postsPerUser; $p++) {
                $url = 'seed-u'.$userId.'-p'.$p;
                $postRows[] = [
                    'user_id' => $userId,
                    'title' => 'Dummy post #'.$p.' (user '.$userId.')',
                    'url' => $url,
                    'content' => '<p>Nội dung seed cho bài <code>'.$url.'</code>.</p>',
                    'thumbnail' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                if (count($postRows) >= $postInsertChunk) {
                    DB::table('posts')->insert($postRows);
                    $postRows = [];
                }
            }
        }

        if ($postRows !== []) {
            DB::table('posts')->insert($postRows);
        }

        $totalPosts = $userCount * $postsPerUser;
        $this->command?->info("Xong: {$userCount} user, {$totalPosts} post. Mật khẩu user: password");
    }

    private function deletePreviousBulkSeed(): void
    {
        $ids = DB::table('users')
            ->where('email', 'like', self::EMAIL_PREFIX.'%@'.self::EMAIL_DOMAIN)
            ->pluck('id');

        if ($ids->isEmpty()) {
            return;
        }

        $this->command?->warn('Xóa dữ liệu seed cũ (email '.self::EMAIL_PREFIX.'*@'.self::EMAIL_DOMAIN.')...');

        $postIds = DB::table('posts')->whereIn('user_id', $ids)->pluck('id');
        if ($postIds->isNotEmpty()) {
            DB::table('likes')->whereIn('post_id', $postIds)->delete();
            DB::table('media')
                ->where('mediable_type', Post::class)
                ->whereIn('mediable_id', $postIds)
                ->delete();

            $commentIds = $this->collectCommentTreeIdsForPosts($postIds);
            if ($commentIds !== []) {
                DB::table('media')
                    ->where('mediable_type', Comment::class)
                    ->whereIn('mediable_id', $commentIds)
                    ->delete();
                DB::table('comments')->whereIn('id', $commentIds)->delete();
            }

            DB::table('posts')->whereIn('id', $postIds)->delete();
        }

        DB::table('user_rules')->whereIn('user_id', $ids)->delete();
        DB::table('users')->whereIn('id', $ids)->delete();
    }

    /**
     * @param  \Illuminate\Support\Collection<int, int|string>  $postIds
     * @return list<int>
     */
    private function collectCommentTreeIdsForPosts($postIds): array
    {
        $postType = Post::class;
        $commentType = Comment::class;

        $roots = DB::table('comments')
            ->where('commentable_type', $postType)
            ->whereIn('commentable_id', $postIds)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $seen = [];
        $queue = $roots;
        foreach ($roots as $id) {
            $seen[$id] = true;
        }

        while ($queue !== []) {
            $batch = array_splice($queue, 0, 500);
            $children = DB::table('comments')
                ->where('commentable_type', $commentType)
                ->whereIn('commentable_id', $batch)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();

            foreach ($children as $cid) {
                if (! isset($seen[$cid])) {
                    $seen[$cid] = true;
                    $queue[] = $cid;
                }
            }
        }

        return array_map('intval', array_keys($seen));
    }
}

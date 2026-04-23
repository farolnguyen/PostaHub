<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use App\Models\UserRule;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SemanticSearchDatasetSeeder extends Seeder
{
    private const USER_EMAIL_DOMAIN = 'semantic.postahub.local';

    private const POST_URL_PREFIX = 'semantic-seed-';

    public function run(): void
    {
        $userCount = max(10, (int) env('SEMANTIC_SEED_USERS', 1000));
        $postsPerUser = max(1, (int) env('SEMANTIC_SEED_POSTS_PER_USER', 100));
        $postCount = max(200, (int) env('SEMANTIC_SEED_POSTS', $userCount * $postsPerUser));

        $this->command?->info("Semantic dataset seeding: {$userCount} users, {$postsPerUser} posts/user ({$postCount} posts total)...");

        DB::disableQueryLog();

        $this->cleanupPreviousSemanticUsersAndPosts();
        $users = $this->seedUsers($userCount);
        $this->seedPosts($users->pluck('id')->all(), $postCount);

        $this->command?->info("Done semantic dataset: {$userCount} users, {$postCount} posts.");
    }

    private function seedUsers(int $userCount)
    {
        $users = collect();

        for ($i = 0; $i < $userCount; $i++) {
            $name = fake('vi_VN')->name();
            $username = Str::slug($name, '.');
            if ($username === '') {
                $username = 'user';
            }
            $email = sprintf('%s.%d@%s', $username, $i, self::USER_EMAIL_DOMAIN);

            $user = User::query()->updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => 'password',
                ]
            );

            UserRule::query()->updateOrCreate(
                ['user_id' => $user->id],
                ['can_post' => true, 'can_comment' => true]
            );

            $users->push($user);
        }

        return $users;
    }

    private function cleanupPreviousSemanticUsersAndPosts(): void
    {
        $postIds = DB::table('posts')
            ->where('url', 'like', self::POST_URL_PREFIX.'%')
            ->pluck('id');

        if ($postIds->isNotEmpty()) {
            $this->deleteByChunks('likes', 'post_id', $postIds->all());
            $this->deleteByChunksWithType('media', 'mediable_type', Post::class, 'mediable_id', $postIds->all());

            $commentIds = $this->collectCommentTreeIdsForPosts($postIds);
            if ($commentIds !== []) {
                $this->deleteByChunksWithType('media', 'mediable_type', Comment::class, 'mediable_id', $commentIds);
                $this->deleteByChunks('comments', 'id', $commentIds);
            }

            $this->deleteByChunks('posts', 'id', $postIds->all());
        }

        $semanticUserIds = DB::table('users')
            ->where('email', 'like', '%@'.self::USER_EMAIL_DOMAIN)
            ->pluck('id');

        if ($semanticUserIds->isNotEmpty()) {
            $this->deleteByChunks('user_rules', 'user_id', $semanticUserIds->all());
            $this->deleteByChunks('users', 'id', $semanticUserIds->all());
        }
    }

    private function seedPosts(array $userIds, int $postCount): void
    {
        $topics = $this->topics();
        $intents = ['hướng_dẫn', 'chia_sẻ_kinh_nghiệm', 'so_sánh', 'tổng_hợp', 'hỏi_đáp'];
        $tones = ['thực_tế', 'dễ_hiểu', 'chi_tiết', 'ngắn_gọn', 'phân_tích'];

        $rows = [];
        $now = now();

        for ($i = 1; $i <= $postCount; $i++) {
            $topic = $topics[array_rand($topics)];
            $intent = $intents[array_rand($intents)];
            $tone = $tones[array_rand($tones)];
            $userId = $userIds[array_rand($userIds)];

            $title = $this->buildTitle($topic, $intent, $tone);
            $content = $this->buildContent($topic, $intent, $tone, $i);

            $rows[] = [
                'user_id' => $userId,
                'title' => $title,
                'url' => self::POST_URL_PREFIX.$i,
                'content' => $content,
                'thumbnail' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($rows) >= 500) {
                DB::table('posts')->insert($rows);
                $rows = [];
            }
        }

        if ($rows !== []) {
            DB::table('posts')->insert($rows);
        }
    }

    private function buildTitle(array $topic, string $intent, string $tone): string
    {
        $keyword = $topic['keywords'][array_rand($topic['keywords'])];
        $entity = $topic['entities'][array_rand($topic['entities'])];
        $angle = $topic['angles'][array_rand($topic['angles'])];
        $adjectives = ['thực chiến', 'dễ áp dụng', 'cơ bản', 'nâng cao', 'thực tế'];
        $adj = $adjectives[array_rand($adjectives)];

        $templates = [
            'hướng_dẫn' => 'Hướng dẫn %s với %s theo cách %s (%s)',
            'chia_sẻ_kinh_nghiệm' => 'Kinh nghiệm %s khi làm việc với %s (%s - %s)',
            'so_sánh' => 'So sánh %s và %s: góc nhìn %s (%s)',
            'tổng_hợp' => 'Tổng hợp %s trong chủ đề %s (%s - %s)',
            'hỏi_đáp' => 'Giải đáp: làm sao để %s khi gặp %s (%s - %s)',
        ];

        $template = $templates[$intent] ?? $templates['tổng_hợp'];

        return sprintf($template, $keyword, $entity, $angle, $tone.' / '.$adj);
    }

    private function buildContent(array $topic, string $intent, string $tone, int $index): string
    {
        $keywordA = $topic['keywords'][array_rand($topic['keywords'])];
        $keywordB = $topic['keywords'][array_rand($topic['keywords'])];
        $entityA = $topic['entities'][array_rand($topic['entities'])];
        $entityB = $topic['entities'][array_rand($topic['entities'])];
        $angle = $topic['angles'][array_rand($topic['angles'])];

        $intro = "Bài viết #{$index} tập trung vào {$keywordA} trong bối cảnh {$entityA}. Mục tiêu là trình bày theo phong cách {$tone}, giúp người đọc tìm được cách áp dụng thực tế.";
        $body1 = "Ở góc độ {$angle}, nhiều người thường nhầm lẫn giữa {$keywordA} và {$keywordB}. Khi đặt vào tình huống cụ thể liên quan đến {$entityB}, cách tiếp cận theo từng bước sẽ hiệu quả hơn việc làm theo cảm tính.";
        $body2 = "Nếu xem đây là bài toán hỏi đáp, câu hỏi trung tâm là: khi nào nên ưu tiên {$keywordA}, khi nào nên chuyển sang {$keywordB}. Câu trả lời phụ thuộc vào mục tiêu, ràng buộc tài nguyên và mức độ ổn định mong muốn.";
        $body3 = "Từ kinh nghiệm thực tế, để tối ưu kết quả cần kết hợp checklist ngắn gọn, đo lường kết quả và lặp vòng cải tiến. Đây là lý do nhóm nội dung này phù hợp để test semantic search với các query diễn đạt tự nhiên.";
        $body4 = "Một góc nhìn khác là sự đánh đổi giữa tốc độ và độ chính xác. Trong bối cảnh {$topic['name']}, nếu không xác định rõ ưu tiên, kết quả thường không ổn định theo thời gian.";
        $body5 = "Trong nhóm người mới, cách học hiệu quả là bắt đầu từ case nhỏ, sau đó mở rộng. Với nhóm đã có kinh nghiệm, cách tiếp cận tốt hơn là benchmark và so sánh theo tiêu chí rõ ràng.";

        $tips = [
            "Bước 1: xác định bài toán trong chủ đề {$topic['name']}.",
            "Bước 2: đối chiếu từ khóa liên quan ({$keywordA}, {$keywordB}, {$entityA}).",
            "Bước 3: chọn hướng xử lý phù hợp với bối cảnh và mục tiêu.",
        ];

        $profile = fake()->randomElement(['short', 'medium', 'long']);

        $parts = ['<p>'.$intro.'</p>', '<p>'.$body1.'</p>'];
        if ($profile !== 'short') {
            $parts[] = '<p>'.$body2.'</p>';
            $parts[] = '<p>'.$body3.'</p>';
        }
        if ($profile === 'long') {
            $parts[] = '<p>'.$body4.'</p>';
            $parts[] = '<p>'.$body5.'</p>';
            $parts[] = '<p>'.$body2.' '.$body4.'</p>';
        }

        $parts[] = '<ul><li>'.$tips[0].'</li><li>'.$tips[1].'</li><li>'.$tips[2].'</li></ul>';

        return implode('', $parts);
    }

    private function topics(): array
    {
        return [
            [
                'name' => 'cong-nghe-lap-trinh',
                'keywords' => ['laravel', 'api', 'hàng đợi', 'bộ nhớ đệm', 'docker', 'postgresql', 'mysql', 'tìm kiếm ngữ nghĩa'],
                'entities' => ['microservice', 'monolith', 'ci/cd', 'đám mây', 'devops'],
                'angles' => ['hiệu năng', 'bảo trì', 'khả năng mở rộng', 'chi phí vận hành'],
            ],
            [
                'name' => 'doi-song-xa-hoi',
                'keywords' => ['thói quen', 'quản lý thời gian', 'sức khỏe tinh thần', 'giao tiếp', 'cân bằng công việc'],
                'entities' => ['gia đình', 'đồng nghiệp', 'cộng đồng', 'môi trường học tập'],
                'angles' => ['thực hành hằng ngày', 'tâm lý hành vi', 'tự đánh giá', 'duy trì động lực'],
            ],
            [
                'name' => 'du-lich-am-thuc',
                'keywords' => ['lịch trình', 'ẩm thực địa phương', 'chi phí', 'trải nghiệm', 'di chuyển'],
                'entities' => ['Đà Nẵng', 'Hội An', 'Hà Nội', 'TP.HCM', 'Đà Lạt'],
                'angles' => ['tiết kiệm ngân sách', 'ưu tiên trải nghiệm', 'đi cùng gia đình', 'đi một mình'],
            ],
            [
                'name' => 'the-thao-suc-khoe',
                'keywords' => ['chạy bộ', 'gym', 'dinh dưỡng', 'hồi phục', 'giấc ngủ'],
                'entities' => ['lịch tập', 'mục tiêu giảm cân', 'mục tiêu tăng cơ', 'nhóm người mới bắt đầu'],
                'angles' => ['an toàn', 'hiệu quả', 'duy trì lâu dài', 'tránh chấn thương'],
            ],
            [
                'name' => 'hoc-tap-nghe-nghiep',
                'keywords' => ['cv', 'phỏng vấn', 'portfolio', 'kỹ năng mềm', 'tự học'],
                'entities' => ['sinh viên', 'người chuyển ngành', 'junior dev', 'team lead'],
                'angles' => ['lập kế hoạch', 'thực chiến', 'phản hồi', 'đánh giá năng lực'],
            ],
        ];
    }

    /**
     * @param  \Illuminate\Support\Collection<int, int|string>  $postIds
     * @return list<int>
     */
    private function collectCommentTreeIdsForPosts($postIds): array
    {
        $postType = Post::class;
        $commentType = Comment::class;

        $postIdArray = collect($postIds)->map(fn ($id) => (int) $id)->all();
        $roots = [];
        foreach (array_chunk($postIdArray, 1000) as $chunk) {
            $chunkRoots = DB::table('comments')
                ->where('commentable_type', $postType)
                ->whereIn('commentable_id', $chunk)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();
            foreach ($chunkRoots as $rid) {
                $roots[] = $rid;
            }
        }

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

    /**
     * @param  list<int|string>  $ids
     */
    private function deleteByChunks(string $table, string $column, array $ids, int $chunkSize = 1000): void
    {
        foreach (array_chunk($ids, $chunkSize) as $chunk) {
            DB::table($table)->whereIn($column, $chunk)->delete();
        }
    }

    /**
     * @param  list<int|string>  $ids
     */
    private function deleteByChunksWithType(
        string $table,
        string $typeColumn,
        string $typeValue,
        string $idColumn,
        array $ids,
        int $chunkSize = 1000
    ): void {
        foreach (array_chunk($ids, $chunkSize) as $chunk) {
            DB::table($table)
                ->where($typeColumn, $typeValue)
                ->whereIn($idColumn, $chunk)
                ->delete();
        }
    }
}


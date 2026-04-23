<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use App\Models\UserRule;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SemanticSearchDatasetSeeder extends Seeder
{
    private const USER_EMAIL_PREFIX = 'semantic-seed-';

    private const USER_EMAIL_DOMAIN = 'postahub.local';

    public function run(): void
    {
        $userCount = max(10, (int) env('SEMANTIC_SEED_USERS', 60));
        $postCount = max(200, (int) env('SEMANTIC_SEED_POSTS', 2500));

        $this->command?->info("Semantic dataset seeding: {$userCount} users, {$postCount} posts...");

        DB::disableQueryLog();

        $users = $this->seedUsers($userCount);
        $this->cleanupPreviousSemanticPosts();
        $this->seedPosts($users->pluck('id')->all(), $postCount);

        $this->command?->info("Done semantic dataset: {$userCount} users, {$postCount} posts.");
    }

    private function seedUsers(int $userCount)
    {
        $users = collect();

        for ($i = 0; $i < $userCount; $i++) {
            $email = self::USER_EMAIL_PREFIX.str_pad((string) $i, 4, '0', STR_PAD_LEFT).'@'.self::USER_EMAIL_DOMAIN;
            $user = User::query()->updateOrCreate(
                ['email' => $email],
                [
                    'name' => 'Semantic User '.str_pad((string) $i, 4, '0', STR_PAD_LEFT),
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

    private function cleanupPreviousSemanticPosts(): void
    {
        $postIds = DB::table('posts')
            ->where('url', 'like', 'semantic-seed-%')
            ->pluck('id');

        if ($postIds->isEmpty()) {
            return;
        }

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

    private function seedPosts(array $userIds, int $postCount): void
    {
        $topics = $this->topics();
        $intents = ['huong_dan', 'chia_se_kinh_nghiem', 'so_sanh', 'tong_hop', 'hoi_dap'];
        $tones = ['thuc_te', 'de_hieu', 'chi_tiet', 'ngan_gon', 'phan_tich'];

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
                'url' => 'semantic-seed-'.$i,
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

        $templates = [
            'huong_dan' => 'Huong dan %s voi %s theo cach %s',
            'chia_se_kinh_nghiem' => 'Kinh nghiem %s khi lam viec voi %s (%s)',
            'so_sanh' => 'So sanh %s va %s: goc nhin %s',
            'tong_hop' => 'Tong hop %s trong chu de %s (%s)',
            'hoi_dap' => 'Giai dap: lam sao de %s khi gap %s (%s)',
        ];

        $template = $templates[$intent] ?? $templates['tong_hop'];

        return sprintf($template, $keyword, $entity, $angle.' - '.$tone);
    }

    private function buildContent(array $topic, string $intent, string $tone, int $index): string
    {
        $keywordA = $topic['keywords'][array_rand($topic['keywords'])];
        $keywordB = $topic['keywords'][array_rand($topic['keywords'])];
        $entityA = $topic['entities'][array_rand($topic['entities'])];
        $entityB = $topic['entities'][array_rand($topic['entities'])];
        $angle = $topic['angles'][array_rand($topic['angles'])];

        $intro = "Bai viet #{$index} tap trung vao {$keywordA} trong boi canh {$entityA}. Muc tieu la trinh bay theo phong cach {$tone}, giup nguoi doc tim duoc cach ap dung thuc te.";
        $body1 = "O goc do {$angle}, nhieu nguoi thuong nham lan giua {$keywordA} va {$keywordB}. Khi dat vao tinh huong cu the lien quan den {$entityB}, cach tiep can theo tung buoc se hieu qua hon viec lam theo cam tinh.";
        $body2 = "Neu xem day la bai toan hoi dap, cau hoi trung tam la: khi nao nen uu tien {$keywordA}, khi nao nen chuyen sang {$keywordB}. Cau tra loi phu thuoc vao muc tieu, rang buoc tai nguyen va muc do on dinh mong muon.";
        $body3 = "Tu kinh nghiem thuc te, de toi uu ket qua can ket hop checklist ngan gon, do luong ket qua va lap vong cai tien. Day la ly do nhom noi dung nay phu hop de test semantic search voi cac query dien dat tu nhien.";

        $tips = [
            "Buoc 1: xac dinh bai toan trong chu de {$topic['name']}.",
            "Buoc 2: doi chieu tu khoa lien quan ({$keywordA}, {$keywordB}, {$entityA}).",
            "Buoc 3: chon huong xu ly phu hop voi boi canh va muc tieu.",
        ];

        return '<p>'.$intro.'</p>'
            .'<p>'.$body1.'</p>'
            .'<p>'.$body2.'</p>'
            .'<p>'.$body3.'</p>'
            .'<ul><li>'.$tips[0].'</li><li>'.$tips[1].'</li><li>'.$tips[2].'</li></ul>';
    }

    private function topics(): array
    {
        return [
            [
                'name' => 'cong-nghe-lap-trinh',
                'keywords' => ['laravel', 'api', 'queue', 'cache', 'docker', 'postgresql', 'mysql', 'semantic search'],
                'entities' => ['microservice', 'monolith', 'ci/cd', 'cloud', 'devops'],
                'angles' => ['hieu nang', 'bao tri', 'kha nang mo rong', 'chi phi van hanh'],
            ],
            [
                'name' => 'doi-song-xa-hoi',
                'keywords' => ['thoi quen', 'quan ly thoi gian', 'suc khoe tinh than', 'giao tiep', 'can bang cong viec'],
                'entities' => ['gia dinh', 'dong nghiep', 'cong dong', 'moi truong hoc tap'],
                'angles' => ['thuc hanh hang ngay', 'tam ly hanh vi', 'tu danh gia', 'duy tri dong luc'],
            ],
            [
                'name' => 'du-lich-am-thuc',
                'keywords' => ['lich trinh', 'am thuc dia phuong', 'chi phi', 'trai nghiem', 'di chuyen'],
                'entities' => ['Da Nang', 'Hoi An', 'Ha Noi', 'TP.HCM', 'Da Lat'],
                'angles' => ['tiet kiem ngan sach', 'uu tien trai nghiem', 'di cung gia dinh', 'di mot minh'],
            ],
            [
                'name' => 'the-thao-suc-khoe',
                'keywords' => ['chay bo', 'gym', 'dinh duong', 'hoi phuc', 'ngu'],
                'entities' => ['lich tap', 'muc tieu giam can', 'muc tieu tang co', 'nhom nguoi moi bat dau'],
                'angles' => ['an toan', 'hieu qua', 'duy tri lau dai', 'tranh chan thuong'],
            ],
            [
                'name' => 'hoc-tap-nghe-nghiep',
                'keywords' => ['cv', 'phong van', 'portfolio', 'ky nang mem', 'tu hoc'],
                'entities' => ['sinh vien', 'nguoi chuyen nganh', 'junior dev', 'team lead'],
                'angles' => ['lap ke hoach', 'thuc chien', 'phan hoi', 'danh gia nang luc'],
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


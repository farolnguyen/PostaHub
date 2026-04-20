<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserRule;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Derrick User',
                'email' => 'derrick@example.com',
                'password' => 'password',
            ],
            [
                'name' => 'Alice Writer',
                'email' => 'alice@example.com',
                'password' => 'password',
            ],
            [
                'name' => 'Bob Reader',
                'email' => 'bob@example.com',
                'password' => 'password',
            ],
        ];

        foreach ($users as $data) {
            $user = User::query()->updateOrCreate(
                ['email' => $data['email']],
                $data
            );

            UserRule::query()->updateOrCreate(
                ['user_id' => $user->id],
                ['can_post' => true, 'can_comment' => true]
            );
        }
    }
}

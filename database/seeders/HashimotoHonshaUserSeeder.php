<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class HashimotoHonshaUserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['login_id' => 'hashimoto_honsha'],
            [
                'name' => 'hashimoto_honsha',
                'password' => bcrypt('Finefine1'),
            ]
        );
    }
}

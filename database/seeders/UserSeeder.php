<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * 사용자(관리자 포함) 데이터를 시드합니다.
     * data/users.json 이 있으면 해당 데이터로 시딩 (현재 DB와 동일).
     */
    public function run(): void
    {
        $path = database_path('seeders/data/users.json');
        if (is_file($path)) {
            $rows = json_decode(file_get_contents($path), true);
            User::query()->delete();
            foreach ($rows as $row) {
                unset($row['remember_token']);
                DB::table('users')->insert($row);
            }
            return;
        }

        User::query()->delete();
        User::create([
            'login_id' => 'homepage',
            'name' => '홈페이지관리자',
            'email' => 'admin@homepage.com',
            'password' => bcrypt('homepagekorea'),
            'role' => 'super_admin',
            'is_active' => true,
        ]);
        User::create([
            'login_id' => 'admin',
            'name' => '관리자',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);
    }
}

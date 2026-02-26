<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingSeeder extends Seeder
{
    /**
     * 기본 설정 데이터를 시드합니다.
     * data/settings.json 이 있으면 해당 데이터로 시딩 (현재 DB와 동일).
     */
    public function run(): void
    {
        $path = database_path('seeders/data/settings.json');
        if (is_file($path)) {
            $rows = json_decode(file_get_contents($path), true);
            Setting::truncate();
            foreach ($rows as $row) {
                DB::table('settings')->insert($row);
            }
            return;
        }

        Setting::truncate();
        Setting::create([
            'id' => 1,
            'site_title' => '홈페이지코리아',
            'site_url' => 'https://homepagekorea.net/',
            'admin_email' => 'cdg9572@gmail.com',
            'company_name' => null,
            'company_address' => 'Hwagok-dong',
            'company_tel' => null,
            'logo_path' => '/storage/settings/9kGYFymMEoyqKb5K3FHuuUk3s3Nnmf6as6Uikj2u.jpg',
            'favicon_path' => null,
            'footer_text' => null,
        ]);
    }
}

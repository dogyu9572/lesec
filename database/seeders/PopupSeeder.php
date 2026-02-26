<?php

namespace Database\Seeders;

use App\Models\Popup;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PopupSeeder extends Seeder
{
    /**
     * 팝업 데이터를 시드합니다.
     * data/popups.json 이 있으면 해당 데이터로 시딩 (현재 DB와 동일).
     */
    public function run(): void
    {
        $path = database_path('seeders/data/popups.json');
        if (!is_file($path)) {
            return;
        }
        $rows = json_decode(file_get_contents($path), true);
        Popup::query()->delete();
        foreach ($rows as $row) {
            DB::table('popups')->insert($row);
        }
    }
}
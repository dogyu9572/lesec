<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ScheduleSeeder extends Seeder
{
    /**
     * 일정 데이터를 시드합니다.
     * data/schedules.json 이 있으면 해당 데이터로 시딩 (현재 DB와 동일).
     */
    public function run(): void
    {
        $path = database_path('seeders/data/schedules.json');
        if (is_file($path)) {
            $rows = json_decode(file_get_contents($path), true);
            DB::table('schedules')->delete();
            foreach ($rows as $row) {
                DB::table('schedules')->insert($row);
            }
            return;
        }
    }
}

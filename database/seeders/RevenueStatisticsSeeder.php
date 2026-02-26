<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RevenueStatisticsSeeder extends Seeder
{
    /**
     * 수익 통계. data/revenue_statistics.json 이 있으면 시딩 (현재 DB와 동일).
     */
    public function run(): void
    {
        $path = database_path('seeders/data/revenue_statistics.json');
        if (!is_file($path)) {
            return;
        }
        $rows = json_decode(file_get_contents($path), true);
        DB::table('revenue_statistics_items')->delete();
        DB::table('revenue_statistics')->delete();
        foreach ($rows as $row) {
            DB::table('revenue_statistics')->insert($row);
        }
    }
}

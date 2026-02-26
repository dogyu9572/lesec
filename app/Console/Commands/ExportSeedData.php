<?php

namespace App\Console\Commands;

use App\Models\Program;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ExportSeedData extends Command
{
    protected $signature = 'db:export-seed-data';

    protected $description = '시더용 테이블 데이터를 JSON으로 내보냅니다. (제외 테이블 외 전부)';

    /** 이관 시 제외할 테이블: 회원/신청/접속로그/로그성/시스템 */
    private const EXCLUDE_TABLES = [
        'members',
        'member_groups',
        'program_reservations',
        'group_applications',
        'group_application_participants',
        'individual_applications',
        'admin_access_logs',
        'user_access_logs',
        'visitor_logs',
        'daily_visitor_stats',
        'mail_sms_logs',
        'mail_sms_message_member',
        'phone_verifications',
        'cache',
        'cache_locks',
        'password_reset_tokens',
        'sessions',
        'jobs',
        'job_batches',
        'failed_jobs',
    ];

    /** export 순서 (FK 의존성 고려) */
    private const EXPORT_ORDER = [
        'admin_menus',
        'admin_groups',
        'admin_group_menu_permissions',
        'users',
        'user_menu_permissions',
        'board_skins',
        'board_templates',
        'categories',
        'boards',
        'settings',
        'banners',
        'popups',
        'programs',
        'schedules',
        'schools',
        'sido_sgg_codes',
        'board_notices',
        'board_library',
        'board_faq',
        'board_greetings',
        'board_contacts',
        'board_privacy-policy',
        'board_comments',
        'revenue_statistics',
        'revenue_statistics_items',
        'mail_sms_messages',
    ];

    public function handle(): int
    {
        $dir = database_path('seeders/data');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $exclude = array_flip(self::EXCLUDE_TABLES);
        $tables = self::EXPORT_ORDER;
        $tables[] = 'board_purpose'; // 테이블 있으면 나중에 추가 export

        foreach ($tables as $table) {
            if (isset($exclude[$table]) || !Schema::hasTable($table)) {
                continue;
            }
            $this->exportTable($dir, $table, $table === 'programs');
        }

        $this->info('내보내기 완료: ' . $dir);
        return self::SUCCESS;
    }

    private function exportTable(string $dir, string $table, bool $useProgramModel): void
    {
        if ($useProgramModel) {
            $rows = Program::orderBy('id')->get()->map(function ($model) {
                $a = $model->toArray();
                $a['period_start'] = $model->period_start?->format('Y-m-d');
                $a['period_end'] = $model->period_end?->format('Y-m-d');
                return $a;
            })->toArray();
        } else {
            $rows = DB::table($table)->orderBy('id')->get()->map(fn ($row) => (array) $row)->toArray();
        }
        $file = str_replace('-', '_', $table) . '.json';
        file_put_contents(
            $dir . '/' . $file,
            json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        );
        $this->line($table . ': ' . count($rows) . '건');
    }
}

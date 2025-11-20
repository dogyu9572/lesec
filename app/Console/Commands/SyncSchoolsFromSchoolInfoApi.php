<?php

namespace App\Console\Commands;

use App\Services\Backoffice\SchoolInfoApiService;
use Illuminate\Console\Command;

class SyncSchoolsFromSchoolInfoApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schools:sync-from-schoolinfo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '학교 알리미 API에서 학교 정보를 동기화합니다.';

    protected $schoolInfoApiService;

    public function __construct(SchoolInfoApiService $schoolInfoApiService)
    {
        parent::__construct();
        $this->schoolInfoApiService = $schoolInfoApiService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('학교 알리미 API 동기화를 시작합니다...');

        $result = $this->schoolInfoApiService->syncAllSchoolsFromApi();

        if ($result['success']) {
            $this->info($result['message']);
            $this->info("동기화된 학교 수: {$result['synced']}개");
            $this->info("신규 등록: {$result['created']}개");
            if ($result['errors'] > 0) {
                $this->warn("오류 발생: {$result['errors']}개");
            }
            return Command::SUCCESS;
        }

        $this->error('동기화 중 오류가 발생했습니다.');
        return Command::FAILURE;
    }
}

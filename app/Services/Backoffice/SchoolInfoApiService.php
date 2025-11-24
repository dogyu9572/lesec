<?php

namespace App\Services\Backoffice;

use App\Models\School;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SchoolInfoApiService
{
    /**
     * 학교 알리미 API 엔드포인트
     */
    private const API_ENDPOINT = 'https://www.schoolinfo.go.kr/openApi.do';

    /**
     * 학교급 코드 매핑
     */
    private const SCHOOL_LEVEL_MAP = [
        '02' => 'elementary',  // 초등학교
        '03' => 'middle',       // 중학교
        '04' => 'high',         // 고등학교
        '05' => null,           // 특수학교 (school_level에 없음)
        '06' => null,           // 그외 (school_level에 없음)
        '07' => null,           // 각종학교 (school_level에 없음)
    ];

    /**
     * API에서 학교 정보 조회
     */
    public function fetchSchools(string $sidoCode, string $sggCode, string $schulKndCode): array
    {
        $apiKey = config('services.schoolinfo.api_key') ?: env('SCHOOLINFO_API_KEY');

        if (empty($apiKey)) {
            Log::error('학교 알리미 API 키가 설정되지 않았습니다.');
            return [];
        }

        try {
            $queryParams = [
                'apiKey' => $apiKey,
                'apiType' => '0',
                'sidoCode' => $sidoCode,
                'sggCode' => $sggCode,
                'schulKndCode' => $schulKndCode,
            ];

            $response = Http::timeout(30)->get(self::API_ENDPOINT, $queryParams);

            if (!$response->successful()) {
                Log::error('학교 알리미 API 호출 실패', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'params' => $queryParams,
                ]);
                return [];
            }

            return $this->parseApiResponse($response->body());

        } catch (\Exception $e) {
            Log::error('학교 알리미 API 호출 중 오류 발생', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'params' => $queryParams ?? [],
            ]);
            return [];
        }
    }

    /**
     * 전체 시도/시군구/학교급 조합으로 일괄 동기화
     */
    public function syncAllSchoolsFromApi(): array
    {
        // 기존 source_type='api' 데이터 삭제
        $deletedCount = School::where('source_type', 'api')->delete();
        Log::info("기존 API 데이터 삭제 완료: {$deletedCount}개");

        $totalSynced = 0;
        $totalCreated = 0;
        $totalErrors = 0;

        // 시도/시군구 코드 조회
        $sidoSggCodes = DB::table('sido_sgg_codes')
            ->where('sido_code', '!=', '00')
            ->where('sgg_code', '!=', '00000')
            ->orderBy('sido_code')
            ->orderBy('sgg_code')
            ->get();

        // 학교급 코드 (02:초등, 03:중등, 04:고등)
        $schoolLevelCodes = ['02', '03', '04'];

        foreach ($sidoSggCodes as $code) {
            foreach ($schoolLevelCodes as $schulKndCode) {
                try {
                    $schools = $this->fetchSchools($code->sido_code, $code->sgg_code, $schulKndCode);

                    if (empty($schools)) {
                        continue;
                    }

                    foreach ($schools as $schoolData) {
                        try {
                            $school = School::updateOrCreate(
                                [
                                    'school_code' => $schoolData['school_code'],
                                ],
                                $schoolData
                            );

                            if ($school->wasRecentlyCreated) {
                                $totalCreated++;
                            }

                            $totalSynced++;
                        } catch (\Exception $e) {
                            $totalErrors++;
                            Log::error('학교 데이터 저장 중 오류', [
                                'message' => $e->getMessage(),
                                'school_data' => $schoolData,
                            ]);
                        }
                    }

                    // API 호출 간 딜레이 (서버 부하 방지)
                    usleep(100000); // 0.1초

                } catch (\Exception $e) {
                    $totalErrors++;
                    Log::error('API 호출 중 오류', [
                        'message' => $e->getMessage(),
                        'sido_code' => $code->sido_code,
                        'sgg_code' => $code->sgg_code,
                        'schul_knd_code' => $schulKndCode,
                    ]);
                }
            }
        }

        return [
            'success' => true,
            'synced' => $totalSynced,
            'created' => $totalCreated,
            'errors' => $totalErrors,
            'message' => "동기화 완료: 총 {$totalSynced}개 (신규: {$totalCreated}개, 오류: {$totalErrors}개)",
        ];
    }

    /**
     * API 응답 파싱 (JSON)
     */
    private function parseApiResponse(string $json): array
    {
        try {
            $data = json_decode($json, true);

            if (!$data || $data['resultCode'] !== 'success') {
                Log::warning('학교 알리미 API 응답 오류', [
                    'resultCode' => $data['resultCode'] ?? 'unknown',
                    'resultMsg' => $data['resultMsg'] ?? 'unknown',
                ]);
                return [];
            }

            if (!isset($data['list']) || !is_array($data['list'])) {
                return [];
            }

            $schools = [];

            foreach ($data['list'] as $item) {
                $schools[] = $this->mapApiDataToSchool($item);
            }

            return $schools;

        } catch (\Exception $e) {
            Log::error('JSON 파싱 오류', [
                'message' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * API 응답을 School 모델 데이터로 변환
     */
    private function mapApiDataToSchool(array $apiData): array
    {
        // ADRCD_NM에서 시도와 시군구 분리 (예: "서울특별시 종로구")
        $city = null;
        $district = null;
        if (isset($apiData['ADRCD_NM']) && !empty($apiData['ADRCD_NM'])) {
            $parts = explode(' ', trim($apiData['ADRCD_NM']));
            $city = $parts[0] ?? null;
            // 나머지 부분을 시군구로 합침 (예: "수원시 장안구" -> "장안구")
            if (count($parts) > 1) {
                $district = implode(' ', array_slice($parts, 1));
            }
        }

        // 학교급 변환
        $schoolLevel = null;
        if (isset($apiData['SCHUL_KND_SC_CODE'])) {
            $schoolLevel = $this->convertSchoolLevel($apiData['SCHUL_KND_SC_CODE']);
        }

        // 날짜 변환 (YYYYMMDD -> YYYY-MM-DD)
        $foundingDate = null;
        if (isset($apiData['FOND_YMD']) && strlen($apiData['FOND_YMD']) === 8) {
            $foundingDate = substr($apiData['FOND_YMD'], 0, 4) . '-' . 
                           substr($apiData['FOND_YMD'], 4, 2) . '-' . 
                           substr($apiData['FOND_YMD'], 6, 2);
        }

        // 주소 조합
        $address = null;
        if (isset($apiData['DTLAD_BRKDN'])) {
            $address = $apiData['DTLAD_BRKDN'];
            if (isset($apiData['ADRCD_NM'])) {
                $address = $apiData['ADRCD_NM'] . ' ' . $address;
            }
        } elseif (isset($apiData['ADRCD_NM'])) {
            $address = $apiData['ADRCD_NM'];
        }

        return [
            'source_type' => 'api',
            'city' => $city,
            'district' => $district,
            'school_level' => $schoolLevel,
            'school_name' => $apiData['SCHUL_NM'] ?? null,
            'school_code' => $apiData['SCHUL_CODE'] ?? null,
            'address' => $address,
            'phone' => $apiData['USER_TELNO'] ?? null,
            'homepage' => $apiData['HMPG_ADRES'] ?? null,
            'is_coed' => $this->convertIsCoed($apiData['COEDU_SC_CODE'] ?? null),
            'day_night_division' => $apiData['DGHT_SC_CODE'] ?? null,
            'founding_date' => $foundingDate,
            'status' => 'normal',
            'last_synced_at' => now(),
        ];
    }

    /**
     * 학교급 코드 변환
     */
    private function convertSchoolLevel(?string $code): ?string
    {
        if (!$code) {
            return null;
        }

        return self::SCHOOL_LEVEL_MAP[$code] ?? null;
    }

    /**
     * 남녀공학 여부 변환
     */
    private function convertIsCoed(?string $code): ?bool
    {
        if (!$code) {
            return null;
        }

        // COEDU_SC_CODE: "남", "여", "남여공학" 등
        return strpos($code, '공학') !== false || $code === '남여공학';
    }
}


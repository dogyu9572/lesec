<?php

namespace App\Services\Backoffice;

use App\Models\School;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NeisApiService
{
    /**
     * NEIS API 엔드포인트
     */
    private const API_ENDPOINT = 'https://open.neis.go.kr/hub/schoolInfo';

    /**
     * NEIS API에서 학교 정보 조회
     */
    public function fetchSchools(array $params = []): array
    {
        $apiKey = env('NEIS_API_KEY');

        if (empty($apiKey)) {
            Log::info('NEIS API 키가 설정되지 않았습니다.');
            return [];
        }

        try {
            $queryParams = [
                'KEY' => $apiKey,
                'Type' => $params['type'] ?? 'xml',
                'pIndex' => $params['pIndex'] ?? 1,
                'pSize' => $params['pSize'] ?? 100,
            ];

            if (!empty($params['school_name'])) {
                $queryParams['SCHUL_NM'] = $params['school_name'];
            }

            if (!empty($params['city_code'])) {
                $queryParams['ATPT_OFCDC_SC_CODE'] = $params['city_code'];
            }

            if (!empty($params['school_code'])) {
                $queryParams['SD_SCHUL_CODE'] = $params['school_code'];
            }

            $response = Http::timeout(10)->get(self::API_ENDPOINT, $queryParams);

            if (!$response->successful()) {
                Log::error('NEIS API 호출 실패', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return [];
            }

            return $this->parseApiResponse($response->body(), $queryParams['Type']);

        } catch (\Exception $e) {
            Log::error('NEIS API 호출 중 오류 발생', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [];
        }
    }

    /**
     * API 데이터를 DB에 동기화
     */
    public function syncSchoolsFromApi(array $params = []): array
    {
        $apiKey = env('NEIS_API_KEY');

        if (empty($apiKey)) {
            Log::info('NEIS API 키가 설정되지 않았습니다.');
            return [
                'success' => false,
                'message' => 'NEIS API 키가 설정되지 않았습니다.',
                'synced' => 0,
            ];
        }

        $schools = $this->fetchSchools($params);

        if (empty($schools)) {
            return [
                'success' => false,
                'message' => 'API에서 학교 정보를 가져올 수 없습니다.',
                'synced' => 0,
            ];
        }

        $syncedCount = 0;
        $updatedCount = 0;
        $createdCount = 0;

        foreach ($schools as $schoolData) {
            $school = School::updateOrCreate(
                [
                    'school_code' => $schoolData['school_code'],
                ],
                [
                    'source_type' => 'api',
                    'city' => $schoolData['city'] ?? null,
                    'district' => $schoolData['district'] ?? null,
                    'school_level' => $this->convertSchoolLevel($schoolData['school_level'] ?? null),
                    'school_name' => $schoolData['school_name'],
                    'address' => $schoolData['address'] ?? null,
                    'phone' => $schoolData['phone'] ?? null,
                    'homepage' => $schoolData['homepage'] ?? null,
                    'is_coed' => $this->convertIsCoed($schoolData['is_coed'] ?? null),
                    'day_night_division' => $schoolData['day_night_division'] ?? null,
                    'founding_date' => $this->convertDate($schoolData['founding_date'] ?? null),
                    'status' => 'normal',
                    'last_synced_at' => now(),
                ]
            );

            if ($school->wasRecentlyCreated) {
                $createdCount++;
            } else {
                $updatedCount++;
            }

            $syncedCount++;
        }

        return [
            'success' => true,
            'message' => "{$syncedCount}개의 학교 정보가 동기화되었습니다. (신규: {$createdCount}, 업데이트: {$updatedCount})",
            'synced' => $syncedCount,
            'created' => $createdCount,
            'updated' => $updatedCount,
        ];
    }

    /**
     * API 응답 파싱
     */
    private function parseApiResponse(string $responseBody, string $type = 'xml'): array
    {
        if ($type === 'json') {
            return $this->parseJsonResponse($responseBody);
        }

        return $this->parseXmlResponse($responseBody);
    }

    /**
     * XML 응답 파싱
     */
    private function parseXmlResponse(string $xml): array
    {
        try {
            $xml = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);

            if (!$xml || !isset($xml->schoolInfo)) {
                return [];
            }

            $schools = [];

            foreach ($xml->schoolInfo->row as $row) {
                $schools[] = [
                    'school_code' => (string) $row->SD_SCHUL_CODE,
                    'city' => (string) $row->LCTN_SC_NM,
                    'district' => (string) $row->JU_ORG_NM,
                    'school_level' => (string) $row->SCHUL_KND_SC_NM,
                    'school_name' => (string) $row->SCHUL_NM,
                    'address' => trim((string) $row->ORG_RDNMA . ' ' . $row->ORG_RDNDA),
                    'phone' => (string) $row->ORG_TELNO,
                    'homepage' => (string) $row->HMPG_ADRES,
                    'is_coed' => (string) $row->COEDU_SC_NM,
                    'day_night_division' => (string) $row->DGHT_SC_NM,
                    'founding_date' => (string) $row->FOND_YMD,
                ];
            }

            return $schools;

        } catch (\Exception $e) {
            Log::error('XML 파싱 오류', [
                'message' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * JSON 응답 파싱
     */
    private function parseJsonResponse(string $json): array
    {
        try {
            $data = json_decode($json, true);

            if (!$data || !isset($data['schoolInfo'])) {
                return [];
            }

            $schools = [];

            foreach ($data['schoolInfo'] as $item) {
                if (!isset($item[0])) {
                    continue;
                }

                foreach ($item[0]['row'] as $row) {
                    $schools[] = [
                        'school_code' => $row['SD_SCHUL_CODE'] ?? '',
                        'city' => $row['LCTN_SC_NM'] ?? '',
                        'district' => $row['JU_ORG_NM'] ?? '',
                        'school_level' => $row['SCHUL_KND_SC_NM'] ?? '',
                        'school_name' => $row['SCHUL_NM'] ?? '',
                        'address' => trim(($row['ORG_RDNMA'] ?? '') . ' ' . ($row['ORG_RDNDA'] ?? '')),
                        'phone' => $row['ORG_TELNO'] ?? '',
                        'homepage' => $row['HMPG_ADRES'] ?? '',
                        'is_coed' => $row['COEDU_SC_NM'] ?? '',
                        'day_night_division' => $row['DGHT_SC_NM'] ?? '',
                        'founding_date' => $row['FOND_YMD'] ?? '',
                    ];
                }
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
     * 학교급 변환
     */
    private function convertSchoolLevel(?string $level): ?string
    {
        if (!$level) {
            return null;
        }

        $levelMap = [
            '초등학교' => 'elementary',
            '중학교' => 'middle',
            '고등학교' => 'high',
        ];

        return $levelMap[$level] ?? null;
    }

    /**
     * 남녀공학 여부 변환
     */
    private function convertIsCoed(?string $coed): ?bool
    {
        if (!$coed) {
            return null;
        }

        return strpos($coed, '남여공학') !== false || strpos($coed, '공학') !== false;
    }

    /**
     * 날짜 변환 (YYYYMMDD -> YYYY-MM-DD)
     */
    private function convertDate(?string $date): ?string
    {
        if (!$date || strlen($date) !== 8) {
            return null;
        }

        return substr($date, 0, 4) . '-' . substr($date, 4, 2) . '-' . substr($date, 6, 2);
    }
}


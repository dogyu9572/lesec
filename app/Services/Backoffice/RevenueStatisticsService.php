<?php

namespace App\Services\Backoffice;

use App\Models\RevenueStatistics;
use App\Models\RevenueStatisticsItem;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RevenueStatisticsService
{
    /**
     * 필터링된 수익 통계 목록을 가져옵니다.
     */
    public function getStatisticsWithFilters(Request $request)
    {
        $query = RevenueStatistics::query();
        
        // 등록일 필터
        if ($request->filled('created_from')) {
            $query->whereDate('created_at', '>=', $request->created_from);
        }
        if ($request->filled('created_to')) {
            $query->whereDate('created_at', '<=', $request->created_to);
        }
        
        // 검색어 필터
        $searchType = $request->get('search_type', 'all');
        $searchTerm = $request->get('search_term');
        
        if ($searchTerm && $searchType !== 'all') {
            if ($searchType === 'title') {
                $query->where('title', 'like', '%' . $searchTerm . '%');
            }
        } elseif ($searchTerm) {
            // 전체 검색
            $query->where('title', 'like', '%' . $searchTerm . '%');
        }
        
        // 목록 개수 설정
        $perPage = $request->get('per_page', 20);
        $perPage = in_array($perPage, [20, 50, 100]) ? $perPage : 20;
        
        return $query->withCount('items')->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * 수익 통계를 생성합니다.
     */
    public function createStatistics(array $data): RevenueStatistics
    {
        $statistics = RevenueStatistics::create([
            'title' => $data['title'],
        ]);
        
        // 통계 항목 추가
        if (isset($data['items']) && is_array($data['items'])) {
            $this->saveItems($statistics->id, $data['items']);
        }
        
        return $statistics;
    }

    /**
     * 수익 통계를 가져옵니다.
     */
    public function getStatistics(int $id): RevenueStatistics
    {
        return RevenueStatistics::with('items')->findOrFail($id);
    }

    /**
     * 수익 통계 정보를 업데이트합니다.
     */
    public function updateStatistics(RevenueStatistics $statistics, array $data): bool
    {
        $updated = $statistics->update([
            'title' => $data['title'],
        ]);
        
        // 통계 항목 업데이트
        if (isset($data['items']) && is_array($data['items'])) {
            $this->saveItems($statistics->id, $data['items']);
        }
        
        return $updated;
    }

    /**
     * 수익 통계를 삭제합니다.
     */
    public function deleteStatistics(RevenueStatistics $statistics): bool
    {
        // 관련 항목들은 cascade로 자동 삭제됨
        return $statistics->delete();
    }

    /**
     * 수익 통계 일괄 삭제
     */
    public function bulkDelete(array $statisticsIds): int
    {
        $deletedCount = 0;
        
        foreach ($statisticsIds as $statisticsId) {
            $statistics = RevenueStatistics::find($statisticsId);
            if ($statistics && $this->deleteStatistics($statistics)) {
                $deletedCount++;
            }
        }
        
        return $deletedCount;
    }

    /**
     * 수익 통계 CSV 다운로드
     */
    public function downloadStatistics(RevenueStatistics $statistics): StreamedResponse
    {
        // 통계 항목 데이터 조회
        $items = $statistics->items()->orderBy('sort_order')->get();
        
        // 다운로드 데이터 변환
        $data = $items->map(function ($item) {
            return [
                'item_name' => $item->item_name ?? '-',
                'participants_count' => $item->participants_count ?? 0,
                'school_name' => $item->school_name ?? '-',
                'revenue' => $item->revenue ?? 0,
            ];
        });
        
        // 컬럼 매핑
        $columnMapping = [
            'item_name' => '항목',
            'participants_count' => '참가인원',
            'school_name' => '참가학교',
            'revenue' => '수익',
        ];
        
        // 모든 컬럼 선택
        $selectedColumns = array_keys($columnMapping);
        
        // 파일명 생성
        $title = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $statistics->title);
        $filename = '수익통계_' . $title . '_' . date('Ymd_His');
        
        // CSV로 다운로드
        return $this->exportToCsv($data, $selectedColumns, $columnMapping, $filename);
    }

    /**
     * CSV 형식으로 다운로드
     */
    private function exportToCsv(Collection $data, array $selectedColumns, array $columnMapping, string $filename): StreamedResponse
    {
        $responseHeaders = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function() use ($data, $selectedColumns, $columnMapping) {
            $file = fopen('php://output', 'w');
            
            // UTF-8 BOM 추가 (한글 깨짐 방지)
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // 헤더 작성
            $csvHeaders = [];
            foreach ($selectedColumns as $column) {
                $csvHeaders[] = $columnMapping[$column] ?? $column;
            }
            fputcsv($file, $csvHeaders);
            
            // 데이터 작성
            foreach ($data as $item) {
                $row = [];
                foreach ($selectedColumns as $column) {
                    $row[] = $item[$column] ?? '-';
                }
                fputcsv($file, $row);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $responseHeaders);
    }

    /**
     * 통계 항목들을 저장합니다.
     */
    private function saveItems(int $statisticsId, array $items): void
    {
        // 기존 항목 삭제
        RevenueStatisticsItem::where('revenue_statistics_id', $statisticsId)->delete();
        
        // 새 항목 추가
        foreach ($items as $index => $item) {
            if (empty($item['item_name'])) {
                continue; // 항목명이 없으면 건너뛰기
            }
            
            RevenueStatisticsItem::create([
                'revenue_statistics_id' => $statisticsId,
                'item_name' => $item['item_name'],
                'participants_count' => $item['participants_count'] ?? 0,
                'school_name' => $item['school_name'] ?? null,
                'revenue' => $item['revenue'] ?? 0,
                'sort_order' => $index,
            ]);
        }
    }
}


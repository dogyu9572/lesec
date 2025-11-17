<?php

namespace App\Services\Concerns;

use Symfony\Component\HttpFoundation\StreamedResponse;

trait DownloadsCsvSample
{
    /**
     * CSV 샘플 파일 다운로드
     *
     * @param string $filename 다운로드될 파일명
     * @param array $headers CSV 헤더 (컬럼명 배열)
     * @param array $sampleRows 샘플 데이터 행 배열 (각 행은 배열)
     * @return StreamedResponse
     */
    protected function downloadCsvSample(string $filename, array $headers, array $sampleRows): StreamedResponse
    {
        $responseHeaders = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        return response()->stream(function () use ($headers, $sampleRows) {
            $out = fopen('php://output', 'w');
            
            // UTF-8 BOM 추가 (한글 깨짐 방지)
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // CSV 헤더 작성
            fputcsv($out, $headers);
            
            // 샘플 데이터 행 작성
            foreach ($sampleRows as $row) {
                fputcsv($out, $row);
            }
            
            fclose($out);
        }, 200, $responseHeaders);
    }
}


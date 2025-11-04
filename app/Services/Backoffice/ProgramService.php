<?php

namespace App\Services\Backoffice;

use App\Models\Program;

class ProgramService
{
    /**
     * 타입별 프로그램 조회 (없으면 생성)
     */
    public function getOrCreateProgram(string $type): Program
    {
        return Program::findOrCreateByType($type);
    }

    /**
     * 프로그램 정보 업데이트
     */
    public function updateProgram(Program $program, array $data): bool
    {
        return $program->update($data);
    }

    /**
     * 모든 프로그램 타입 목록 반환
     */
    public function getAllTypes(): array
    {
        return [
            'middle_semester' => '중등학기',
            'middle_vacation' => '중등방학',
            'high_semester' => '고등학기',
            'high_vacation' => '고등방학',
            'special' => '특별프로그램',
        ];
    }

    /**
     * 타입 한글명 반환
     */
    public function getTypeName(string $type): string
    {
        $types = $this->getAllTypes();
        return $types[$type] ?? $type;
    }
}

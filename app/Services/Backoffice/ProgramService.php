<?php

namespace App\Services\Backoffice;

use App\Models\Program;

class ProgramService
{
    /**
     * 타입별 프로그램 조회 (없으면 생성)
     */
    public function getOrCreateProgram(string $type, string $applicationType = 'individual'): Program
    {
        return Program::findOrCreateByTypeAndApplicationType($type, $applicationType);
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
     * 모든 탭 목록 반환 (타입 + 신청유형 조합)
     */
    public function getAllTabs(): array
    {
        $types = $this->getAllTypes();
        $tabs = [];

        foreach ($types as $typeKey => $typeName) {
            $tabs[$typeKey . '_individual'] = $typeName . ' 개인';
            $tabs[$typeKey . '_group'] = $typeName . ' 단체';
        }

        return $tabs;
    }

    /**
     * 탭 키에서 타입과 신청유형 분리
     */
    public function parseTabKey(string $tabKey): array
    {
        $parts = explode('_', $tabKey);
        $lastPart = array_pop($parts);

        if ($lastPart === 'individual' || $lastPart === 'group') {
            $applicationType = $lastPart;
            $type = implode('_', $parts);
        } else {
            // 기존 형식 호환성 유지
            $type = $tabKey;
            $applicationType = 'individual';
        }

        return [
            'type' => $type,
            'application_type' => $applicationType,
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

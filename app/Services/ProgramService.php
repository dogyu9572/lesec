<?php

namespace App\Services;

use App\Models\Program;

class ProgramService
{
    private const AVAILABLE_TYPES = [
        'middle_semester',
        'middle_vacation',
        'high_semester',
        'high_vacation',
        'special',
    ];
    
    private const DEFAULT_TYPE = 'middle_semester';
    
    /**
     * 타입별 프로그램 조회
     */
    public function getProgramByType(string $type): ?Program
    {
        return Program::byType($type)->active()->first();
    }
    
    /**
     * 타입 한글명 반환
     */
    public function getTypeName(string $type): string
    {
        $types = [
            'middle_semester' => '중등학기',
            'middle_vacation' => '중등방학',
            'high_semester' => '고등학기',
            'high_vacation' => '고등방학',
            'special' => '특별프로그램',
        ];
        
        return $types[$type] ?? '';
    }
    
    /**
     * 타입별 서브 메뉴 번호 반환
     */
    public function getSubMenuNumber(string $type): string
    {
        $numbers = [
            'middle_semester' => '01',
            'middle_vacation' => '02',
            'high_semester' => '03',
            'high_vacation' => '04',
            'special' => '05',
        ];
        
        return $numbers[$type] ?? '01';
    }

    /**
     * 타입별 단체 신청 교육 선택 페이지 라우트 반환
     */
    public function getGroupApplySelectRoute(string $type): string
    {
        $resolvedType = in_array($type, self::AVAILABLE_TYPES, true) ? $type : self::DEFAULT_TYPE;
        
        return route('program.select.group', $resolvedType);
    }

    /**
     * 타입별 개인 신청 교육 선택 페이지 라우트 반환
     */
    public function getIndividualApplySelectRoute(string $type): string
    {
        $resolvedType = in_array($type, self::AVAILABLE_TYPES, true) ? $type : self::DEFAULT_TYPE;
        
        return route('program.select.individual', $resolvedType);
    }
}

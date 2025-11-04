<?php

namespace App\Services;

use App\Models\Program;

class ProgramService
{
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
        $routeMap = [
            'middle_semester' => '/program/middle_semester_apply_a2',
            'middle_vacation' => '/program/middle_vacation_apply_a2',
            'high_semester' => '/program/high_semester_apply_a2',
            'high_vacation' => '/program/high_vacation_apply_a2',
            'special' => '/program/special_apply_a2',
        ];
        return $routeMap[$type] ?? '/program/middle_semester_apply_a2';
    }

    /**
     * 타입별 개인 신청 교육 선택 페이지 라우트 반환
     */
    public function getIndividualApplySelectRoute(string $type): string
    {
        $routeMap = [
            'middle_semester' => '/program/middle_semester_apply_b2',
            'middle_vacation' => '/program/middle_vacation_apply_b2',
            'high_semester' => '/program/high_semester_apply_b2',
            'high_vacation' => '/program/high_vacation_apply_b2',
            'special' => '/program/special_apply_b2',
        ];
        return $routeMap[$type] ?? '/program/middle_semester_apply_b2';
    }
}

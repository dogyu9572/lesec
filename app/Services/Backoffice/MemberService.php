<?php

namespace App\Services\Backoffice;

use App\Models\Member;
use Illuminate\Support\Facades\Hash;

class MemberService
{
    /**
     * 필터링된 회원 목록을 가져옵니다.
     */
    public function getMembersWithFilters(\Illuminate\Http\Request $request)
    {
        $query = $this->buildFilterQuery($request);
        $perPage = $request->get('per_page', 20);
        $perPage = in_array($perPage, [20, 50, 100]) ? $perPage : 20;
        
        return $query->paginate($perPage);
    }
    
    /**
     * 필터 쿼리 빌드 (공통)
     */
    private function buildFilterQuery(\Illuminate\Http\Request $request)
    {
        $query = Member::with('memberGroup');
        
        // 선택된 회원 ID 필터 (엑셀 다운로드용)
        if ($request->filled('member_ids')) {
            $memberIds = is_array($request->member_ids) ? $request->member_ids : [$request->member_ids];
            $query->whereIn('id', $memberIds);
        }
        
        // 회원구분 필터
        if ($request->filled('member_type')) {
            $query->where('member_type', $request->member_type);
        }
        
        // 회원그룹 필터
        if ($request->filled('member_group_id')) {
            $query->where('member_group_id', $request->member_group_id);
        }
        
        // 소속 시/도 필터
        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }
        
        // 가입일 필터
        if ($request->filled('joined_from')) {
            $query->whereDate('joined_at', '>=', $request->joined_from);
        }
        if ($request->filled('joined_to')) {
            $query->whereDate('joined_at', '<=', $request->joined_to);
        }
        
        // 수신동의 필터
        if ($request->filled('email_consent')) {
            $query->where('email_consent', $request->email_consent);
        }
        if ($request->filled('sms_consent')) {
            $query->where('sms_consent', $request->sms_consent);
        }
        if ($request->filled('kakao_consent')) {
            $query->where('kakao_consent', $request->kakao_consent);
        }
        
        // 검색어 필터
        if ($request->filled('search_keyword')) {
            $searchType = $request->get('search_type', 'all');
            $searchKeyword = $request->search_keyword;
            $normalizedKeyword = str_replace('-', '', $searchKeyword);
            
            if ($searchType === 'all') {
                // 전체 검색
                $query->where(function($q) use ($searchKeyword, $normalizedKeyword) {
                    $q->where('login_id', 'like', "%{$searchKeyword}%")
                      ->orWhere('name', 'like', "%{$searchKeyword}%")
                      ->orWhere('school_name', 'like', "%{$searchKeyword}%")
                      ->orWhere('email', 'like', "%{$searchKeyword}%")
                      ->orWhere('contact', 'like', "%{$normalizedKeyword}%")
                      ->orWhere('city', 'like', "%{$searchKeyword}%")
                      ->orWhere('grade', 'like', "%{$searchKeyword}%");
                });
            } else {
                // 특정 필드 검색
                if ($searchType === 'contact') {
                    // 연락처 검색 시 하이픈 제거하여 검색 (DB에는 하이픈 없는 값으로 저장됨)
                    $query->where('contact', 'like', "%{$normalizedKeyword}%");
                } elseif ($searchType === 'grade') {
                    // 학년 검색 시 숫자로 변환하여 정확한 일치 검색
                    if (is_numeric($searchKeyword)) {
                        $query->where('grade', (int)$searchKeyword);
                    }
                } else {
                    $query->where($searchType, 'like', "%{$searchKeyword}%");
                }
            }
        }
        
        return $query->orderBy('created_at', 'desc');
    }
    
    /**
     * 회원을 생성합니다.
     */
    public function createMember(array $data): Member
    {
        $memberData = [
            'login_id' => $data['login_id'],
            'password' => Hash::make($data['password']),
            'member_type' => $data['member_type'],
            'member_group_id' => $data['member_group_id'] ?? null,
            'name' => $data['name'],
            'email' => $data['email'],
            'birth_date' => $data['birth_date'] ?? null,
            'gender' => $data['gender'] ?? null,
            'contact' => $this->normalizePhoneNumber($data['contact'] ?? null),
            'parent_contact' => $this->normalizePhoneNumber($data['parent_contact'] ?? null),
            'city' => $data['city'] ?? null,
            'district' => $data['district'] ?? null,
            'school_name' => $data['school_name'] ?? null,
            'school_id' => $data['school_id'] ?? null,
            'grade' => $data['grade'] ?? null,
            'class_number' => $data['class_number'] ?? null,
            'email_consent' => $data['email_consent'] ?? false,
            'sms_consent' => $data['sms_consent'] ?? false,
            'joined_at' => now(),
        ];
        
        return Member::create($memberData);
    }
    
    /**
     * 회원 정보를 업데이트합니다.
     */
    public function updateMember(Member $member, array $data): bool
    {
        $member->member_type = $data['member_type'] ?? $member->member_type;
        $member->member_group_id = $data['member_group_id'] ?? $member->member_group_id;
        $member->name = $data['name'] ?? $member->name;
        $member->email = $data['email'] ?? $member->email;
        $member->birth_date = $data['birth_date'] ?? $member->birth_date;
        $member->gender = $data['gender'] ?? $member->gender;
        $member->contact = isset($data['contact']) ? $this->normalizePhoneNumber($data['contact']) : $member->contact;
        $member->parent_contact = isset($data['parent_contact']) ? $this->normalizePhoneNumber($data['parent_contact']) : $member->parent_contact;
        $member->city = $data['city'] ?? $member->city;
        $member->district = $data['district'] ?? $member->district;
        $member->school_name = $data['school_name'] ?? $member->school_name;
        $member->school_id = $data['school_id'] ?? $member->school_id;
        $member->grade = $data['grade'] ?? $member->grade;
        $member->class_number = $data['class_number'] ?? $member->class_number;
        $member->email_consent = isset($data['email_consent']) ? (bool)$data['email_consent'] : false;
        $member->sms_consent = isset($data['sms_consent']) ? (bool)$data['sms_consent'] : false;
        $member->kakao_consent = isset($data['kakao_consent']) ? (bool)$data['kakao_consent'] : false;
        $member->memo = $data['memo'] ?? $member->memo;
        
        if (!empty($data['password'])) {
            $member->password = Hash::make($data['password']);
        }
        
        return $member->save();
    }
    
    /**
     * 회원을 삭제합니다.
     */
    public function deleteMember(Member $member): bool
    {
        return $member->delete();
    }
    
    /**
     * 회원 일괄 삭제
     */
    public function bulkDelete(array $memberIds): int
    {
        return Member::whereIn('id', $memberIds)->delete();
    }
    
    /**
     * 전화번호 정규화 (숫자만)
     */
    private function normalizePhoneNumber(?string $phoneNumber): ?string
    {
        if (empty($phoneNumber)) {
            return null;
        }
        
        $digits = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        return $digits !== '' ? $digits : null;
    }
    
    /**
     * Excel 다운로드용 회원 목록 가져오기
     */
    public function getMembersForExcel(\Illuminate\Http\Request $request)
    {
        return $this->buildFilterQuery($request)->get();
    }
}


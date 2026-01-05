<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Http\Requests\Member\MemberOverFourteenRegisterRequest;
use App\Http\Requests\Member\MemberUnderFourteenRegisterRequest;
use App\Models\Member;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Services\Member\MemberRegisterService;

class MemberRegisterController extends Controller
{
    private const REGISTRATION_SESSION_KEY = 'member_registration';
    private const COMPLETED_SESSION_KEY = 'member_registration_completed';

    public function __construct(private readonly MemberRegisterService $registerService)
    {
    }

    /**
     * 회원 구분 선택 화면
     */
    public function showTypeSelection(Request $request): \Illuminate\View\View
    {
        $this->clearRegistrationSession($request);

        $gNum = '00';
        $sNum = '02';
        $gName = '회원가입';
        $sName = '회원가입';

        return view('member.register', compact('gNum', 'sNum', 'gName', 'sName'));
    }

    /**
     * 연령대 선택 화면
     */
    public function showAgeSelection(Request $request)
    {
        $memberType = $request->query('member_type');

        if ($memberType !== null) {
            if (!in_array($memberType, ['teacher', 'student'], true)) {
                return redirect()
                    ->route('member.register')
                    ->withErrors(['member_type' => '유효하지 않은 회원 구분입니다.']);
            }

            $this->storeRegistrationSession($request, [
                'member_type' => $memberType,
            ]);
        } else {
            $memberType = $this->getRegistrationSession($request, 'member_type');
            if ($memberType === null) {
                return redirect()
                    ->route('member.register')
                    ->withErrors(['member_type' => '회원 구분을 먼저 선택해주세요.']);
            }
        }

        $gNum = '00';
        $sNum = '02';
        $gName = '회원가입';
        $sName = '회원가입';

        return view('member.register2', compact('gNum', 'sNum', 'gName', 'sName', 'memberType'));
    }

    /**
     * 14세 미만 인증 안내
     */
    public function showUnderFourteenVerification(Request $request)
    {
        $memberType = $this->getRegistrationSession($request, 'member_type');
        if ($memberType === null) {
            return redirect()
                ->route('member.register')
                ->withErrors(['member_type' => '회원 구분을 먼저 선택해주세요.']);
        }

        if ($memberType !== 'student') {
            return redirect()
                ->route('member.register2')
                ->withErrors(['age_group' => '14세 미만 회원가입은 학생만 가능합니다.']);
        }

        $this->storeRegistrationSession($request, [
            'age_group' => 'under14',
        ]);

        $gNum = '00';
        $sNum = '02';
        $gName = '회원가입';
        $sName = '회원가입';

        return view('member.register2_a', compact('gNum', 'sNum', 'gName', 'sName'));
    }

    /**
     * 14세 이상 인증 안내
     */
    public function showOverFourteenVerification(Request $request)
    {
        $memberType = $this->getRegistrationSession($request, 'member_type');
        if ($memberType === null) {
            return redirect()
                ->route('member.register')
                ->withErrors(['member_type' => '회원 구분을 먼저 선택해주세요.']);
        }

        $this->storeRegistrationSession($request, [
            'age_group' => 'over14',
        ]);

        $gNum = '00';
        $sNum = '02';
        $gName = '회원가입';
        $sName = '회원가입';

        return view('member.register2_b', compact('gNum', 'sNum', 'gName', 'sName', 'memberType'));
    }

    /**
     * 14세 미만 회원 정보 입력 화면
     */
    public function showUnderFourteenForm(Request $request)
    {
        if (!$this->isValidFlow($request, 'under14')) {
            return redirect()
                ->route('member.register')
                ->withErrors(['process' => '회원가입 절차를 처음부터 다시 진행해주세요.']);
        }

        $gNum = '00';
        $sNum = '02';
        $gName = '회원가입';
        $sName = '회원정보 입력';

        return view('member.register3_a', compact('gNum', 'sNum', 'gName', 'sName'));
    }

    /**
     * 14세 미만 회원 가입 처리
     */
    public function registerUnderFourteen(MemberUnderFourteenRegisterRequest $request): RedirectResponse
    {
        if (!$this->isValidFlow($request, 'under14')) {
            return redirect()
                ->route('member.register')
                ->withErrors(['process' => '회원가입 절차를 처음부터 다시 진행해주세요.']);
        }

        $member = $this->createMemberRecord($request->validated(), $request, [
            'grade' => null,
            'class_number' => null,
            'parent_contact' => null,
        ]);

        $this->finalizeRegistration($request, $member);

        return redirect()->route('member.register4');
    }

    /**
     * 14세 이상 회원 정보 입력 화면
     */
    public function showOverFourteenForm(Request $request)
    {
        if (!$this->isValidFlow($request, 'over14')) {
            return redirect()
                ->route('member.register')
                ->withErrors(['process' => '회원가입 절차를 처음부터 다시 진행해주세요.']);
        }

        $gNum = '00';
        $sNum = '02';
        $gName = '회원가입';
        $sName = '회원정보 입력';

        $memberType = $this->getRegistrationSession($request, 'member_type');

        return view('member.register3_b', compact('gNum', 'sNum', 'gName', 'sName', 'memberType'));
    }

    /**
     * 14세 이상 회원 가입 처리
     */
    public function registerOverFourteen(MemberOverFourteenRegisterRequest $request): RedirectResponse
    {
        if (!$this->isValidFlow($request, 'over14')) {
            return redirect()
                ->route('member.register')
                ->withErrors(['process' => '회원가입 절차를 처음부터 다시 진행해주세요.']);
        }

        $member = $this->createMemberRecord(
            $request->validated(),
            $request,
            [
                'grade' => $request->input('grade'),
                'class_number' => $request->input('class_number'),
                'parent_contact' => $request->input('parent_contact'),
            ]
        );

        $this->finalizeRegistration($request, $member);

        return redirect()->route('member.register4');
    }

    /**
     * 중복 확인
     */
    public function checkDuplicate(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'field' => ['required', 'in:login_id,contact,email'],
            'value' => ['nullable', 'string', 'max:255'],
        ], [
            'field.required' => '검증 항목이 누락되었습니다.',
            'field.in' => '검증할 수 없는 항목입니다.',
        ]);

        $field = $request->input('field');
        $value = trim((string) $request->input('value', ''));

        if ($value === '') {
            return response()->json([
                'success' => false,
                'message' => '값을 입력해주세요.',
            ], 422);
        }

        // 아이디 길이 검증 (4자 이상)
        if ($field === 'login_id' && strlen($value) < 4) {
            return response()->json([
                'success' => false,
                'message' => '아이디는 최소 4자 이상 입력해주세요.',
            ], 422);
        }

        if ($field === 'email') {
            $value = $this->registerService->normalizeEmailForCheck($value);

            if ($value === null) {
                return response()->json([
                    'success' => false,
                    'message' => '올바른 이메일 형식으로 입력해주세요.',
                ], 422);
            }
        }

        if ($field === 'contact') {
            $value = $this->registerService->normalizeContact($value);

            if ($value === null) {
                return response()->json([
                    'success' => false,
                    'message' => '휴대폰 번호를 정확히 입력해주세요.',
                ], 422);
            }
        }

        $exists = Member::query()
            ->when($field === 'contact', function ($query) use ($value) {
                $query->where(function ($nested) use ($value) {
                    $nested->where('contact', $value);

                    $formatted = $this->registerService->formatContactForDisplay($value);
                    if ($formatted !== null) {
                        $nested->orWhere('contact', $formatted);
                    }
                });
            }, function ($query) use ($field, $value) {
                $query->where($field, $value);
            })
            ->exists();

        if ($exists) {
            $messages = [
                'login_id' => '입력하신 아이디로 이미 계정이 존재합니다.',
                'contact' => '입력하신 연락처로 이미 계정이 존재합니다.',
                'email' => '입력하신 이메일로 이미 계정이 존재합니다.',
            ];

            return response()->json([
                'success' => false,
                'message' => $messages[$field] ?? '이미 사용 중입니다.',
            ]);
        }

        $successMessages = [
            'login_id' => '사용 가능한 아이디 입니다.',
            'contact' => '사용 가능한 연락처 입니다.',
            'email' => '사용 가능한 이메일 입니다.',
        ];

        return response()->json([
            'success' => true,
            'message' => $successMessages[$field] ?? '사용 가능한 값입니다.',
        ]);
    }

    /**
     * 회원가입 완료 페이지
     */
    public function showComplete(Request $request)
    {
        $completed = $request->session()->pull(self::COMPLETED_SESSION_KEY);

        if ($completed === null) {
            return redirect()
                ->route('member.register')
                ->withErrors(['process' => '정상적인 가입 완료 단계가 아닙니다.']);
        }

        $gNum = '00';
        $sNum = '02';
        $gName = '회원가입';
        $sName = '회원가입 완료';

        $completedLoginId = $completed['login_id'] ?? null;

        return view('member.register4', compact('gNum', 'sNum', 'gName', 'sName', 'completedLoginId'));
    }

    /**
     * 회원 레코드 생성
     */
    private function createMemberRecord(array $data, Request $request, array $additional): Member
    {
        $sessionData = $this->getRegistrationSession($request);
        $memberType = $sessionData['member_type'] ?? 'student';

        // Normalize contact numbers and delegate creation to service
        $data['contact'] = $this->registerService->normalizeContact($data['contact'] ?? '');
        $additional['parent_contact'] = $this->registerService->normalizeContact($additional['parent_contact'] ?? '');

        return $this->registerService->createMember($data, $additional, $memberType);
    }

    /**
     * 회원가입 세션 정리
     */
    private function finalizeRegistration(Request $request, Member $member): void
    {
        $request->session()->put(self::COMPLETED_SESSION_KEY, [
            'member_id' => $member->id,
            'login_id' => $member->login_id,
        ]);

        $this->clearRegistrationSession($request);
    }

    /**
     * 회원가입 플로우 유효성 검사
     */
    private function isValidFlow(Request $request, string $requiredAgeGroup): bool
    {
        $memberType = $this->getRegistrationSession($request, 'member_type');
        $ageGroup = $this->getRegistrationSession($request, 'age_group');

        if ($memberType === null || $ageGroup === null) {
            return false;
        }

        if ($requiredAgeGroup === 'under14' && $memberType !== 'student') {
            return false;
        }

        return $ageGroup === $requiredAgeGroup;
    }

    /**
     * 연락처 숫자만 남기기
     */
    private function normalizeContactNumber(?string $number): ?string
    {
        if (empty($number)) {
            return null;
        }

        $digits = preg_replace('/[^0-9]/', '', $number);

        return $digits !== '' ? $digits : null;
    }

    /**
     * 연락처 표시용 하이픈 처리
     */
    private function formatContactNumberForDisplay(?string $digits): ?string
    {
        if (empty($digits)) {
            return null;
        }

        if (strlen($digits) === 11) {
            return preg_replace('/(\d{3})(\d{4})(\d{4})/', '$1-$2-$3', $digits);
        }

        if (strlen($digits) === 10) {
            return preg_replace('/(\d{3})(\d{3})(\d{4})/', '$1-$2-$3', $digits);
        }

        return $digits;
    }

    /**
     * 세션 저장
     */
    private function storeRegistrationSession(Request $request, array $data): void
    {
        $existing = $request->session()->get(self::REGISTRATION_SESSION_KEY, []);
        $request->session()->put(self::REGISTRATION_SESSION_KEY, array_merge($existing, $data));
    }

    /**
     * 세션 값 조회
     */
    private function getRegistrationSession(Request $request, ?string $key = null)
    {
        $data = $request->session()->get(self::REGISTRATION_SESSION_KEY, []);

        return $key === null ? $data : ($data[$key] ?? null);
    }

    /**
     * 세션 초기화
     */
    private function clearRegistrationSession(Request $request): void
    {
        $request->session()->forget(self::REGISTRATION_SESSION_KEY);
    }

    /**
     * 중복 확인용 이메일 정규화
     */
    private function normalizeEmailForCheck(string $email): ?string
    {
        $email = trim($email);

        if ($email === '') {
            return null;
        }

        if (!str_contains($email, '@')) {
            return null;
        }

        $parts = explode('@', $email);

        if (count($parts) !== 2) {
            return null;
        }

        [$id, $domain] = $parts;

        if ($id === '' || $domain === '') {
            return null;
        }

        $normalized = $id . '@' . $domain;

        return filter_var($normalized, FILTER_VALIDATE_EMAIL) ? $normalized : null;
    }
}


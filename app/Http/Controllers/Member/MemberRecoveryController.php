<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Http\Requests\Member\MemberFindIdRequest;
use App\Http\Requests\Member\MemberFindPasswordRequest;
use App\Http\Requests\Member\MemberPasswordUpdateRequest;
use App\Models\Member;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Services\Member\MemberRecoveryService;

class MemberRecoveryController extends Controller
{
    private const FIND_ID_SESSION_KEY = 'member_find_id_result';
    private const PASSWORD_RESET_SESSION_KEY = 'member_password_reset';
    private const PASSWORD_RESET_DONE_KEY = 'member_password_reset_done';

    public function __construct(private readonly MemberRecoveryService $recoveryService)
    {
    }

    /**
     * 아이디 찾기 화면
     */
    public function showFindIdForm(): \Illuminate\View\View
    {
        $gNum = '00';
        $sNum = '03';
        $gName = '회원가입';
        $sName = '아이디 찾기';

        return view('member.find_id', compact('gNum', 'sNum', 'gName', 'sName'));
    }

    /**
     * 아이디 찾기 처리
     */
    public function findId(MemberFindIdRequest $request)
    {
        $contact = $this->recoveryService->normalizeContact($request->input('contact'));

        if ($contact === null) {
            return back()
                ->withErrors(['contact' => '휴대폰 번호를 정확히 입력해주세요.'])
                ->withInput();
        }

        $member = $this->recoveryService->findMemberByNameAndContact($request->input('name'), $contact);

        if ($member === null) {
            return back()
                ->withErrors(['find_id_failed' => '일치하는 회원 정보를 찾을 수 없습니다.'])
                ->withInput();
        }

        $request->session()->put(self::FIND_ID_SESSION_KEY, [
            'masked_login_id' => $this->maskLoginId($member->login_id),
            'joined_at' => $member->joined_at instanceof CarbonInterface
                ? $member->joined_at->format('Y-m-d')
                : null,
        ]);

        return redirect()->route('member.find_id_end');
    }

    /**
     * 아이디 찾기 결과
     */
    public function showFindIdResult(Request $request)
    {
        $result = $request->session()->pull(self::FIND_ID_SESSION_KEY);

        if ($result === null) {
            return redirect()
                ->route('member.find_id')
                ->withErrors(['find_id_flow' => '아이디 찾기 정보를 다시 입력해주세요.']);
        }

        $gNum = '00';
        $sNum = '03';
        $gName = '회원가입';
        $sName = '아이디 찾기';

        return view('member.find_id_end', [
            'gNum' => $gNum,
            'sNum' => $sNum,
            'gName' => $gName,
            'sName' => $sName,
            'result' => $result,
        ]);
    }

    /**
     * 비밀번호 변경 요청 화면
     */
    public function showFindPasswordForm(): \Illuminate\View\View
    {
        $gNum = '00';
        $sNum = '03';
        $gName = '회원가입';
        $sName = '비밀번호 변경';

        return view('member.find_pw', compact('gNum', 'sNum', 'gName', 'sName'));
    }

    /**
     * 비밀번호 변경 대상 확인
     */
    public function verifyForPassword(MemberFindPasswordRequest $request)
    {
        $contact = $this->recoveryService->normalizeContact($request->input('contact'));

        if ($contact === null) {
            return back()
                ->withErrors(['contact' => '휴대폰 번호를 정확히 입력해주세요.'])
                ->withInput();
        }

        $member = $this->recoveryService->findMemberForPassword($request->input('login_id'), $request->input('name'), $contact);

        if ($member === null) {
            return back()
                ->withErrors(['find_pw_failed' => '일치하는 회원 정보를 찾을 수 없습니다.'])
                ->withInput();
        }

        $request->session()->put(self::PASSWORD_RESET_SESSION_KEY, [
            'member_id' => $member->id,
        ]);

        return redirect()->route('member.find_pw_change');
    }

    /**
     * 비밀번호 변경 화면
     */
    public function showPasswordChangeForm(Request $request)
    {
        $resetInfo = $request->session()->get(self::PASSWORD_RESET_SESSION_KEY);

        if ($resetInfo === null) {
            return redirect()
                ->route('member.find_pw')
                ->withErrors(['find_pw_flow' => '비밀번호 변경을 위한 정보를 먼저 입력해주세요.']);
        }

        $gNum = '00';
        $sNum = '03';
        $gName = '회원가입';
        $sName = '비밀번호 변경';

        return view('member.find_pw_change', compact('gNum', 'sNum', 'gName', 'sName'));
    }

    /**
     * 비밀번호 변경 처리
     */
    public function updatePassword(MemberPasswordUpdateRequest $request)
    {
        $resetInfo = $request->session()->get(self::PASSWORD_RESET_SESSION_KEY);

        if ($resetInfo === null) {
            return redirect()
                ->route('member.find_pw')
                ->withErrors(['find_pw_flow' => '비밀번호 변경을 위한 정보를 먼저 입력해주세요.']);
        }

        $member = Member::find($resetInfo['member_id']);

        if ($member === null) {
            $request->session()->forget(self::PASSWORD_RESET_SESSION_KEY);

            return redirect()
                ->route('member.find_pw')
                ->withErrors(['find_pw_flow' => '회원 정보를 다시 확인해주세요.']);
        }

        $this->recoveryService->updatePassword($member, $request->input('password'));

        $request->session()->forget(self::PASSWORD_RESET_SESSION_KEY);
        $request->session()->put(self::PASSWORD_RESET_DONE_KEY, true);

        return redirect()->route('member.find_pw_end');
    }

    /**
     * 비밀번호 변경 완료 화면
     */
    public function showPasswordChangeResult(Request $request)
    {
        $completed = $request->session()->pull(self::PASSWORD_RESET_DONE_KEY);

        if ($completed === null) {
            return redirect()
                ->route('member.find_pw')
                ->withErrors(['find_pw_flow' => '비밀번호 변경을 다시 진행해주세요.']);
        }

        $gNum = '00';
        $sNum = '03';
        $gName = '회원가입';
        $sName = '비밀번호 변경';

        return view('member.find_pw_end', compact('gNum', 'sNum', 'gName', 'sName'));
    }

    /**
     * 아이디 마스킹
     */
    private function maskLoginId(string $loginId): string
    {
        $length = mb_strlen($loginId);

        if ($length <= 2) {
            return str_repeat('*', $length);
        }

        $visible = mb_substr($loginId, 0, 3);
        $maskedLength = max($length - 3, 0);

        return $visible . str_repeat('*', $maskedLength);
    }
}



<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SubController extends Controller
{
//프로그램
	//중등학기
    public function middle_semester()
    {
        $gNum = "01"; $sNum = "01"; $gName = "프로그램"; $sName = "중등학기";
        return view('program.middle_semester', compact('gNum', 'sNum', 'gName', 'sName'));
    }
	//중등학기 - 신청 - 단체
    public function middle_semester_apply_a()
    {
        $gNum = "01"; $sNum = "01"; $gName = "프로그램"; $sName = "중등학기";
        return view('program.middle_semester_apply_a', compact('gNum', 'sNum', 'gName', 'sName'));
    }
	//중등학기 - 신청 - 단체 - 교육 선택
    public function middle_semester_apply_a2()
    {
        $gNum = "01"; $sNum = "01"; $gName = "프로그램"; $sName = "중등학기";
        return view('program.middle_semester_apply_a2', compact('gNum', 'sNum', 'gName', 'sName'));
    }
	//중등학기 - 신청 - 단체 - 완료
    public function middle_semester_apply_a_end()
    {
        $gNum = "01"; $sNum = "01"; $gName = "프로그램"; $sName = "중등학기";
        return view('program.middle_semester_apply_a_end', compact('gNum', 'sNum', 'gName', 'sName'));
    }
	//중등학기 - 신청 - 개인
    public function middle_semester_apply_b()
    {
        $gNum = "01"; $sNum = "01"; $gName = "프로그램"; $sName = "중등학기";
        return view('program.middle_semester_apply_b', compact('gNum', 'sNum', 'gName', 'sName'));
    }
	//중등학기 - 신청 - 개인 - 교육 선택
    public function middle_semester_apply_b2()
    {
        $gNum = "01"; $sNum = "01"; $gName = "프로그램"; $sName = "중등학기";
        return view('program.middle_semester_apply_b2', compact('gNum', 'sNum', 'gName', 'sName'));
    }
	//중등학기 - 신청 - 개인 - 완료
    public function middle_semester_apply_b_end()
    {
        $gNum = "01"; $sNum = "01"; $gName = "프로그램"; $sName = "중등학기";
        return view('program.middle_semester_apply_b_end', compact('gNum', 'sNum', 'gName', 'sName'));
    }
	//중등방학
    public function middle_vacation()
    {
        $gNum = "01"; $sNum = "02"; $gName = "프로그램"; $sName = "중등방학";
        return view('program.middle_vacation', compact('gNum', 'sNum', 'gName', 'sName'));
    }
	//고등학기
    public function high_semester()
    {
        $gNum = "01"; $sNum = "03"; $gName = "프로그램"; $sName = "고등학기";
        return view('program.high_semester', compact('gNum', 'sNum', 'gName', 'sName'));
    }
	//고등방학
    public function high_vacation()
    {
        $gNum = "01"; $sNum = "04"; $gName = "프로그램"; $sName = "고등방학";
        return view('program.high_vacation', compact('gNum', 'sNum', 'gName', 'sName'));
    }
	//특별프로그램
    public function special()
    {
        $gNum = "01"; $sNum = "05"; $gName = "프로그램"; $sName = "특별프로그램";
        return view('program.special', compact('gNum', 'sNum', 'gName', 'sName'));
    }
//게시판
	//공지사항
    public function notice()
    {
        $gNum = "02"; $sNum = "01"; $gName = "게시판"; $sName = "공지사항";
        return view('board.notice', compact('gNum', 'sNum', 'gName', 'sName'));
    }
	//공지사항 - 상세
    public function notice_view()
    {
        $gNum = "02"; $sNum = "01"; $gName = "게시판"; $sName = "공지사항";
        return view('board.notice_view', compact('gNum', 'sNum', 'gName', 'sName'));
    }
	//FAQ
    public function faq()
    {
        $gNum = "02"; $sNum = "02"; $gName = "게시판"; $sName = "FAQ";
        return view('board.faq', compact('gNum', 'sNum', 'gName', 'sName'));
    }
	//자료실
    public function dataroom()
    {
        $gNum = "02"; $sNum = "03"; $gName = "게시판"; $sName = "자료실";
        return view('board.dataroom', compact('gNum', 'sNum', 'gName', 'sName'));
    }
	//자료실 - 상세
    public function dataroom_view()
    {
        $gNum = "02"; $sNum = "03"; $gName = "게시판"; $sName = "자료실";
        return view('board.dataroom_view', compact('gNum', 'sNum', 'gName', 'sName'));
    }
//마이페이지
	//회원정보
    public function member()
    {
        $gNum = "03"; $sNum = "01"; $gName = "마이페이지"; $sName = "회원정보";
        return view('mypage.member', compact('gNum', 'sNum', 'gName', 'sName'));
    }
	//신청내역 - 단체목록
    public function application_list()
    {
        $gNum = "03"; $sNum = "02"; $gName = "마이페이지"; $sName = "회원정보";
        return view('mypage.application_list', compact('gNum', 'sNum', 'gName', 'sName'));
    }
	//신청내역 - 단체상세
    public function application_view()
    {
        $gNum = "03"; $sNum = "02"; $gName = "마이페이지"; $sName = "회원정보";
        return view('mypage.application_view', compact('gNum', 'sNum', 'gName', 'sName'));
    }
	//신청내역 - 단체쓰기
    public function application_write()
    {
        $gNum = "03"; $sNum = "02"; $gName = "마이페이지"; $sName = "회원정보";
        return view('mypage.application_write', compact('gNum', 'sNum', 'gName', 'sName'));
    }
	//신청내역 - 개인목록
    public function application_indi_list()
    {
        $gNum = "03"; $sNum = "02"; $gName = "마이페이지"; $sName = "회원정보";
        return view('mypage.application_indi_list', compact('gNum', 'sNum', 'gName', 'sName'));
    }
	//신청내역 - 개인상세
    public function application_indi_view()
    {
        $gNum = "03"; $sNum = "02"; $gName = "마이페이지"; $sName = "회원정보";
        return view('mypage.application_indi_view', compact('gNum', 'sNum', 'gName', 'sName'));
    }
//센터소개
	//인사말
    public function greeting()
    {
        $gNum = "04"; $sNum = "01"; $gName = "센터소개"; $sName = "인사말";
        return view('introduction.greeting', compact('gNum', 'sNum', 'gName', 'sName'));
    }
	//설립목적
    public function establishment()
    {
        $gNum = "04"; $sNum = "02"; $gName = "센터소개"; $sName = "인사말";
        return view('introduction.establishment', compact('gNum', 'sNum', 'gName', 'sName'));
    }
	//연락처
    public function contact()
    {
        $gNum = "04"; $sNum = "03"; $gName = "센터소개"; $sName = "인사말";
        return view('introduction.contact', compact('gNum', 'sNum', 'gName', 'sName'));
    }
//위치안내
	//오시는 길
    public function location()
    {
        $gNum = "05"; $sNum = "01"; $gName = "위치안내"; $sName = "오시는 길";
        return view('location.location', compact('gNum', 'sNum', 'gName', 'sName'));
    }
	//강의실 안내
    public function classroom()
    {
        $gNum = "05"; $sNum = "02"; $gName = "위치안내"; $sName = "강의실 안내";
        return view('location.classroom', compact('gNum', 'sNum', 'gName', 'sName'));
    }
	//주차 안내
    public function parking()
    {
        $gNum = "05"; $sNum = "03"; $gName = "위치안내"; $sName = "주차 안내";
        return view('location.parking', compact('gNum', 'sNum', 'gName', 'sName'));
    }
//member
	//로그인
    public function login()
    {
        $gNum = "00"; $sNum = "01"; $gName = "로그인"; $sName = "로그인";
        return view('member.login', compact('gNum', 'sNum', 'gName', 'sName'));
    }
	//회원가입
    public function register()
    {
        $gNum = "00"; $sNum = "02"; $gName = "회원가입"; $sName = "회원가입";
        return view('member.register', compact('gNum', 'sNum', 'gName', 'sName'));
    }
    public function register2()
    {
        $gNum = "00"; $sNum = "02"; $gName = "회원가입"; $sName = "회원가입";
        return view('member.register2', compact('gNum', 'sNum', 'gName', 'sName'));
    }
    public function register2_a()
    {
        $gNum = "00"; $sNum = "02"; $gName = "회원가입"; $sName = "회원가입";
        return view('member.register2_a', compact('gNum', 'sNum', 'gName', 'sName'));
    }
    public function register2_b()
    {
        $gNum = "00"; $sNum = "02"; $gName = "회원가입"; $sName = "회원가입";
        return view('member.register2_b', compact('gNum', 'sNum', 'gName', 'sName'));
    }
    public function register3_a()
    {
        $gNum = "00"; $sNum = "02"; $gName = "회원가입"; $sName = "회원가입";
        return view('member.register3_a', compact('gNum', 'sNum', 'gName', 'sName'));
    }
    public function register3_b()
    {
        $gNum = "00"; $sNum = "02"; $gName = "회원가입"; $sName = "회원가입";
        return view('member.register3_b', compact('gNum', 'sNum', 'gName', 'sName'));
    }
    public function register4()
    {
        $gNum = "00"; $sNum = "02"; $gName = "회원가입"; $sName = "회원가입";
        return view('member.register4', compact('gNum', 'sNum', 'gName', 'sName'));
    }
    public function find_id()
    {
        $gNum = "00"; $sNum = "03"; $gName = "회원가입"; $sName = "아이디 찾기";
        return view('member.find_id', compact('gNum', 'sNum', 'gName', 'sName'));
    }
    public function find_id_end()
    {
        $gNum = "00"; $sNum = "03"; $gName = "회원가입"; $sName = "아이디 찾기";
        return view('member.find_id_end', compact('gNum', 'sNum', 'gName', 'sName'));
    }
    public function find_pw()
    {
        $gNum = "00"; $sNum = "03"; $gName = "회원가입"; $sName = "비밀번호 변경";
        return view('member.find_pw', compact('gNum', 'sNum', 'gName', 'sName'));
    }
    public function find_pw_change()
    {
        $gNum = "00"; $sNum = "03"; $gName = "회원가입"; $sName = "비밀번호 변경";
        return view('member.find_pw_change', compact('gNum', 'sNum', 'gName', 'sName'));
    }
    public function find_pw_end()
    {
        $gNum = "00"; $sNum = "03"; $gName = "회원가입"; $sName = "비밀번호 변경";
        return view('member.find_pw_end', compact('gNum', 'sNum', 'gName', 'sName'));
    }
//약관
	//개인정보처리방침
    public function privacy_policy()
    {
        $gNum = "terms"; $sNum = ""; $gName = "개인정보처리방침"; $sName = "";
        return view('terms.privacy_policy', compact('gNum', 'sNum', 'gName', 'sName'));
    }
	//이메일무단수집거부
    public function no_email_collection()
    {
        $gNum = "terms"; $sNum = ""; $gName = "이메일무단수집거부"; $sName = "";
        return view('terms.no_email_collection', compact('gNum', 'sNum', 'gName', 'sName'));
    }
//인쇄
	//견적서
    public function estimate()
    {
        $gNum = "print"; $sNum = ""; $gName = "견적서"; $sName = "";
        return view('print.estimate', compact('gNum', 'sNum', 'gName', 'sName'));
    }
//오류
	//404 페이지 없음
    public function error404()
    {
        $gNum = "00"; $sNum = ""; $gName = "404"; $sName = "";
        return view('error.error404', compact('gNum', 'sNum', 'gName', 'sName'));
    }
}
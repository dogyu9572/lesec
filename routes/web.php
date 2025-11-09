<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SubController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\Backoffice\PopupController;

// =============================================================================
// 기본 라우트 파일
// =============================================================================

// 메인 페이지
Route::get('/', [HomeController::class, 'index'])->name('home');

// 팝업 표시 (일반 팝업용)
Route::get('/popup/{popup}', [PopupController::class, 'showPopup'])->name('popup.show');

// 인증 관련 라우트
Route::prefix('auth')->name('auth.')->group(function () {
    // 로그인
    Route::get('/login', [LoginController::class, 'showLoginForm'])
        ->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::post('/logout', [LoginController::class, 'logout'])
        ->name('logout');

    // 회원가입
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])
        ->name('register');
    Route::post('/register', [RegisterController::class, 'register']);

    // 비밀번호 재설정
    Route::prefix('password')->name('password.')->group(function () {
        Route::get('/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])
            ->name('request');
        Route::post('/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])
            ->name('email');
        Route::get('/reset/{token}', [ResetPasswordController::class, 'showResetForm'])
            ->name('reset');
        Route::post('/reset', [ResetPasswordController::class, 'reset'])
            ->name('update');
    });
});

// =============================================================================
// 서브페이지 관련 라우트
// =============================================================================

// 서브페이지 관련 라우트
Route::prefix('sub')->name('sub.')->group(function () {
    Route::get('/sample', [SubController::class, 'sample'])->name('sample');
});

//프로그램
Route::prefix('program')->name('program.')->group(function () {
	// 통합 라우트 (타입별 유형 선택, 단체 신청, 개인 신청)
	Route::get('/{type}', [ProgramController::class, 'show'])
		->where('type', 'middle_semester|middle_vacation|high_semester|high_vacation|special')
		->name('show');
	
	Route::get('/{type}/apply-group', [ProgramController::class, 'applyGroup'])
		->where('type', 'middle_semester|middle_vacation|high_semester|high_vacation|special')
		->name('apply.group');
	
	Route::get('/{type}/apply-individual', [ProgramController::class, 'applyIndividual'])
		->where('type', 'middle_semester|middle_vacation|high_semester|high_vacation|special')
		->name('apply.individual');
	
	// 교육 선택 페이지
	Route::get('/{type}/select-group', [ProgramController::class, 'selectGroup'])
		->where('type', 'middle_semester|middle_vacation|high_semester|high_vacation|special')
		->name('select.group');
	
	Route::get('/{type}/select-individual', [ProgramController::class, 'selectIndividual'])
		->where('type', 'middle_semester|middle_vacation|high_semester|high_vacation|special')
		->name('select.individual');
	
	// 완료 페이지
	Route::get('/{type}/complete-group', [ProgramController::class, 'completeGroup'])
		->where('type', 'middle_semester|middle_vacation|high_semester|high_vacation|special')
		->name('complete.group');
	
	Route::get('/{type}/complete-individual', [ProgramController::class, 'completeIndividual'])
		->where('type', 'middle_semester|middle_vacation|high_semester|high_vacation|special')
		->name('complete.individual');
	
});

//게시판
Route::prefix('board')->name('board.')->group(function () {
	//공지사항
	Route::get('/notice', [SubController::class, 'notice'])->name('notice');
	Route::get('/notice_view', [SubController::class, 'notice_view'])->name('notice_view');
	//FAQ
	Route::get('/faq', [SubController::class, 'faq'])->name('faq');
	//자료실
	Route::get('/dataroom', [SubController::class, 'dataroom'])->name('dataroom');
	Route::get('/dataroom_view', [SubController::class, 'dataroom_view'])->name('dataroom_view');
});

//마이페이지
Route::prefix('mypage')->name('mypage.')->group(function () {
	//회원정보
	Route::get('/member', [SubController::class, 'member'])->name('member');
	//신청내역 - 단체
	Route::get('/application_list', [SubController::class, 'application_list'])->name('application_list');
	Route::get('/application_view', [SubController::class, 'application_view'])->name('application_view');
	Route::get('/application_write', [SubController::class, 'application_write'])->name('application_write');
	//신청내역 - 개인
	Route::get('/application_indi_list', [SubController::class, 'application_indi_list'])->name('application_indi_list');
	Route::get('/application_indi_view', [SubController::class, 'application_indi_view'])->name('application_indi_view');
});

//센터소개
Route::prefix('introduction')->name('introduction.')->group(function () {
	//인사말
	Route::get('/greeting', [SubController::class, 'greeting'])->name('greeting');
	//설립목적
	Route::get('/establishment', [SubController::class, 'establishment'])->name('establishment');
	//연락처
	Route::get('/contact', [SubController::class, 'contact'])->name('contact');
});

//위치안내
Route::prefix('location')->name('location.')->group(function () {
	//오시는 길
	Route::get('/location', [SubController::class, 'location'])->name('location');
	//강의실 안내
	Route::get('/classroom', [SubController::class, 'classroom'])->name('classroom');
	//주차 안내
	Route::get('/parking', [SubController::class, 'parking'])->name('parking');
});

//멤버
Route::prefix('member')->name('member.')->group(function () {
	//로그인
	Route::get('/login', [SubController::class, 'login'])->name('login');
	//회원가입
	Route::get('/register', [SubController::class, 'register'])->name('register');
	Route::get('/register2', [SubController::class, 'register2'])->name('register2');
	Route::get('/register2_a', [SubController::class, 'register2_a'])->name('register2_a');
	Route::get('/register2_b', [SubController::class, 'register2_b'])->name('register2_b');
	Route::get('/register3_a', [SubController::class, 'register3_a'])->name('register3_a');
	Route::get('/register3_b', [SubController::class, 'register3_b'])->name('register3_b');
	Route::get('/register4', [SubController::class, 'register4'])->name('register4');
	//아이디비번찾기
	Route::get('/find_id', [SubController::class, 'find_id'])->name('find_id');
	Route::get('/find_id_end', [SubController::class, 'find_id_end'])->name('find_id_end');
	Route::get('/find_pw', [SubController::class, 'find_pw'])->name('find_pw');
	Route::get('/find_pw_change', [SubController::class, 'find_pw_change'])->name('find_pw_change');
	Route::get('/find_pw_end', [SubController::class, 'find_pw_end'])->name('find_pw_end');
});

//약관들
Route::prefix('terms')->name('terms.')->group(function () {
	//개인정보처리방침
	Route::get('/privacy_policy', [SubController::class, 'privacy_policy'])->name('privacy_policy');
	//이메일무단수집거부
	Route::get('/no_email_collection', [SubController::class, 'no_email_collection'])->name('no_email_collection');
});

//인쇄
Route::prefix('print')->name('print.')->group(function () {
	//견적서
	Route::get('/estimate', [SubController::class, 'estimate'])->name('estimate');
});

//오류
Route::prefix('error')->name('error.')->group(function () {
	//404 페이지 없음
	Route::get('/error404', [SubController::class, 'error404'])->name('error404');
});

// =============================================================================
// 분리된 라우트 파일들 포함
// =============================================================================

// 백오피스 라우트 (관리자 전용)
require __DIR__.'/backoffice.php';
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SubController;
use App\Http\Controllers\Introduction\GreetingContactController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\Backoffice\PopupController;
use App\Http\Controllers\Member\MemberAuthController;
use App\Http\Controllers\Member\MemberRegisterController;
use App\Http\Controllers\Member\MemberMypageController;
use App\Http\Controllers\Member\MemberRecoveryController;
use App\Http\Controllers\Member\SchoolSearchController;

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
	Route::post('/{type}/apply-individual', [ProgramController::class, 'submitIndividualApplication'])
		->where('type', 'middle_semester|middle_vacation|high_semester|high_vacation|special')
		->name('apply.individual.submit');
	
	Route::post('/{type}/apply-group', [ProgramController::class, 'submitGroupApplication'])
		->where('type', 'middle_semester|middle_vacation|high_semester|high_vacation|special')
		->name('apply.group.submit');
	
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
	Route::get('/notice/attachments/{postId}/{attachmentIndex}', [SubController::class, 'downloadBoardAttachment'])
		->whereNumber('postId')
		->whereNumber('attachmentIndex')
		->defaults('boardType', 'notice')
		->name('notice.attachment');
	Route::get('/notice/{postId}', [SubController::class, 'notice_view'])
		->whereNumber('postId')
		->name('notice.view');
	//FAQ
	Route::get('/faq', [SubController::class, 'faq'])->name('faq');
	Route::get('/faq/attachments/{postId}/{attachmentIndex}', [SubController::class, 'downloadBoardAttachment'])
		->whereNumber('postId')
		->whereNumber('attachmentIndex')
		->defaults('boardType', 'faq')
		->name('faq.attachment');
	//자료실
	Route::get('/dataroom', [SubController::class, 'dataroom'])->name('dataroom');
	Route::get('/dataroom/attachments/{postId}/{attachmentIndex}', [SubController::class, 'downloadBoardAttachment'])
		->whereNumber('postId')
		->whereNumber('attachmentIndex')
		->defaults('boardType', 'dataroom')
		->name('dataroom.attachment');
	Route::get('/dataroom/{postId}', [SubController::class, 'dataroom_view'])
		->whereNumber('postId')
		->name('dataroom.view');
});

//마이페이지
Route::prefix('mypage')->name('mypage.')->group(function () {
	//회원정보
	Route::get('/member', [MemberMypageController::class, 'show'])->name('member');
	Route::post('/member', [MemberMypageController::class, 'update'])->name('member.update');
	//신청내역 - 단체
	Route::get('/application_list', [MemberMypageController::class, 'groupApplicationList'])->name('application_list');
	Route::post('/application_cancel/{id}', [MemberMypageController::class, 'cancelGroupApplication'])->name('application_cancel');
	Route::get('/application_view/{id}', [MemberMypageController::class, 'groupApplicationShow'])->name('application_view');
	Route::get('/application_write/{id}', [MemberMypageController::class, 'groupApplicationWrite'])->name('application_write');
	Route::post('/application_write/{id}', [MemberMypageController::class, 'groupApplicationWriteUpdate'])->name('application_write.update');
	Route::get('/application_write/{id}/sample', [MemberMypageController::class, 'groupApplicationWriteSample'])->name('application_write.sample');
	Route::post('/application_write/{id}/upload', [MemberMypageController::class, 'groupApplicationWriteUpload'])->name('application_write.upload');
	//신청내역 - 개인
	Route::get('/application_indi_list', [MemberMypageController::class, 'individualApplicationList'])->name('application_indi_list');
	Route::get('/application_indi_view/{id}', [MemberMypageController::class, 'individualApplicationShow'])->name('application_indi_view');
	Route::post('/application_indi_cancel/{id}', [MemberMypageController::class, 'cancelIndividualApplication'])->name('application_indi_cancel');
});

//센터소개
Route::prefix('introduction')->name('introduction.')->group(function () {
	//인사말
	Route::get('/greeting', [GreetingContactController::class, 'greeting'])->name('greeting');
	//설립목적
	Route::get('/establishment', [SubController::class, 'establishment'])->name('establishment');
	//연락처
	Route::get('/contact', [GreetingContactController::class, 'contact'])->name('contact');
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
	// 로그인
	Route::get('/login', [MemberAuthController::class, 'showLoginForm'])->name('login');
	Route::post('/login', [MemberAuthController::class, 'login'])->name('login.submit');
	Route::post('/logout', [MemberAuthController::class, 'logout'])->name('logout');

	// 회원가입
	Route::get('/register', [MemberRegisterController::class, 'showTypeSelection'])->name('register');
	Route::get('/register2', [MemberRegisterController::class, 'showAgeSelection'])->name('register2');
	Route::get('/register2_a', [MemberRegisterController::class, 'showUnderFourteenVerification'])->name('register2_a');
	Route::get('/register2_b', [MemberRegisterController::class, 'showOverFourteenVerification'])->name('register2_b');
	Route::get('/register3_a', [MemberRegisterController::class, 'showUnderFourteenForm'])->name('register3_a');
	Route::post('/register3_a', [MemberRegisterController::class, 'registerUnderFourteen'])->name('register3_a.submit');
	Route::get('/register3_b', [MemberRegisterController::class, 'showOverFourteenForm'])->name('register3_b');
	Route::post('/register3_b', [MemberRegisterController::class, 'registerOverFourteen'])->name('register3_b.submit');
	Route::get('/register4', [MemberRegisterController::class, 'showComplete'])->name('register4');
	Route::post('/check-duplicate', [MemberRegisterController::class, 'checkDuplicate'])->name('register.check.duplicate');
	Route::post('/sms-verification/send', [MemberRegisterController::class, 'sendSmsVerification'])->name('sms.verification.send');
	Route::post('/sms-verification/verify', [MemberRegisterController::class, 'verifySmsCode'])->name('sms.verification.verify');
	Route::get('/schools/search', [SchoolSearchController::class, 'search'])->name('schools.search');

	// 아이디 / 비밀번호 찾기
	Route::get('/find_id', [MemberRecoveryController::class, 'showFindIdForm'])->name('find_id');
	Route::post('/find_id', [MemberRecoveryController::class, 'findId'])->name('find_id.submit');
	Route::get('/find_id_end', [MemberRecoveryController::class, 'showFindIdResult'])->name('find_id_end');
	Route::get('/find_pw', [MemberRecoveryController::class, 'showFindPasswordForm'])->name('find_pw');
	Route::post('/find_pw', [MemberRecoveryController::class, 'verifyForPassword'])->name('find_pw.submit');
	Route::get('/find_pw_change', [MemberRecoveryController::class, 'showPasswordChangeForm'])->name('find_pw_change');
	Route::post('/find_pw_change', [MemberRecoveryController::class, 'updatePassword'])->name('find_pw_change.submit');
	Route::get('/find_pw_end', [MemberRecoveryController::class, 'showPasswordChangeResult'])->name('find_pw_end');
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
	Route::get('/estimate', [MemberMypageController::class, 'printEstimate'])->name('estimate');
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
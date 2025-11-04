<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Backoffice\AuthController;
use App\Http\Controllers\Backoffice\AdminMenuController;
use App\Http\Controllers\Backoffice\CategoryController;
use App\Http\Controllers\Backoffice\SettingController;
use App\Http\Controllers\Backoffice\BoardController;
use App\Http\Controllers\Backoffice\BoardTemplateController;
use App\Http\Controllers\Backoffice\BoardSkinController;
use App\Http\Controllers\Backoffice\BoardPostController;
use App\Http\Controllers\Backoffice\UserController;
use App\Http\Controllers\Backoffice\LogController;
use App\Http\Controllers\Backoffice\AdminController;
use App\Http\Controllers\Backoffice\AdminGroupController;
use App\Http\Controllers\Backoffice\MemberController;
use App\Http\Controllers\Backoffice\MemberGroupController;
use App\Http\Controllers\Backoffice\BannerController;
use App\Http\Controllers\Backoffice\PopupController;
use App\Http\Controllers\Backoffice\ProgramController;
use App\Http\Controllers\Backoffice\GroupProgramController;

// =============================================================================
// 백오피스 인증 라우트
// =============================================================================
Route::prefix('backoffice')->name('backoffice.')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])
        ->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/logout', [AuthController::class, 'logout'])
        ->name('logout');
});

// =============================================================================
// 백오피스 라우트 (관리자 전용)
// =============================================================================

Route::prefix('backoffice')->middleware(['backoffice'])->group(function () {
    
    // 대시보드
    Route::get('/', [App\Http\Controllers\Backoffice\DashboardController::class, 'index'])
        ->name('backoffice.dashboard');
    
    // 대시보드 API
    Route::get('/api/statistics', [App\Http\Controllers\Backoffice\DashboardController::class, 'statistics'])
        ->name('backoffice.api.statistics');

    // -------------------------------------------------------------------------
    // 시스템 관리
    // -------------------------------------------------------------------------

    // 관리자 메뉴 관리
    Route::resource('admin-menus', AdminMenuController::class, [
        'names' => 'backoffice.admin-menus'
    ])->except(['show']);

    // 메뉴 순서 업데이트
    Route::post('admin-menus/update-order', [AdminMenuController::class, 'updateOrder'])
        ->name('backoffice.admin-menus.update-order');
    
    // 메뉴 부모 업데이트 (드래그로 메뉴 이동)
    Route::post('admin-menus/update-parent', [AdminMenuController::class, 'updateParent'])
        ->name('backoffice.admin-menus.update-parent');

    // 카테고리 관리
    // 카테고리 순서 업데이트 (resource 라우트보다 앞에 위치)
    Route::post('categories/update-order', [CategoryController::class, 'updateOrder'])
        ->name('backoffice.categories.update-order');

    // 활성 카테고리 조회 (AJAX - resource 라우트보다 앞에 위치)
    Route::get('categories/active/{group}', [CategoryController::class, 'getActiveCategories'])
        ->name('backoffice.categories.active');

    // 특정 그룹의 1차 카테고리 조회 (AJAX)
    Route::get('categories/get-by-group/{groupId}', [CategoryController::class, 'getByGroup'])
        ->name('backoffice.categories.get-by-group');

    // 카테고리 수정용 데이터 조회 (AJAX)
    Route::get('categories/{category}/edit-data', [CategoryController::class, 'getEditData'])
        ->name('backoffice.categories.edit-data');

    // 인라인 수정 (AJAX)
    Route::post('categories/{category}/update-inline', [CategoryController::class, 'updateInline'])
        ->name('backoffice.categories.update-inline');

    // 모달 등록 (AJAX)
    Route::post('categories/store-modal', [CategoryController::class, 'storeModal'])
        ->name('backoffice.categories.store-modal');

    // 모달 수정 (AJAX)
    Route::put('categories/update-modal', [CategoryController::class, 'updateModal'])
        ->name('backoffice.categories.update-modal');

    // 미리 생성될 코드 조회 (AJAX)
    Route::post('categories/generate-preview-code', [CategoryController::class, 'generatePreviewCode'])
        ->name('backoffice.categories.generate-preview-code');

    Route::resource('categories', CategoryController::class, [
        'names' => 'backoffice.categories'
    ])->except(['show']);

    // 기본설정 관리
    Route::get('setting', [SettingController::class, 'index'])
        ->name('backoffice.setting.index');
    Route::post('setting', [SettingController::class, 'update'])
        ->name('backoffice.setting.update');

    // 접속 로그 관리
    Route::get('logs/access', [LogController::class, 'access'])
        ->name('backoffice.logs.access');
    Route::get('logs/user-access', [LogController::class, 'userAccessLogs'])
        ->name('backoffice.logs.user-access');
    Route::get('logs/admin-access', [LogController::class, 'adminAccessLogs'])
        ->name('backoffice.logs.admin-access');

    // 관리자 계정 관리
    Route::post('admins/bulk-destroy', [AdminController::class, 'bulkDestroy'])
        ->name('backoffice.admins.bulk-destroy');
    Route::post('admins/check-login-id', [AdminController::class, 'checkLoginId'])
        ->name('backoffice.admins.check-login-id');
    Route::resource('admins', AdminController::class, [
        'names' => 'backoffice.admins'
    ]);

    // 관리자 권한 그룹 관리
    Route::resource('admin-groups', AdminGroupController::class, [
        'names' => 'backoffice.admin-groups'
    ])->except(['show']);

    // 권한 그룹 권한 설정
    Route::get('admin-groups/{admin_group}/permissions', [AdminGroupController::class, 'editPermissions'])
        ->name('backoffice.admin-groups.permissions.edit');
    Route::post('admin-groups/{admin_group}/permissions', [AdminGroupController::class, 'updatePermissions'])
        ->name('backoffice.admin-groups.permissions.update');

    // -------------------------------------------------------------------------
    // 콘텐츠 관리
    // -------------------------------------------------------------------------

    // 이미지 업로드
    Route::post('upload-image', function (Request $request) {
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $path = $file->store('uploads/editor', 'public');

            return response()->json([
                'uploaded' => true,
                'url' => asset('storage/' . $path)
            ]);
        }

        return response()->json([
            'uploaded' => false,
            'error' => ['message' => '이미지 업로드에 실패했습니다.']
        ]);
    });

    // 정렬 순서 업데이트
    Route::post('board-posts/update-sort-order', [BoardPostController::class, 'updateSortOrder'])->name('backoffice.board-posts.update-sort-order');

    // 게시글 관리 (특정 게시판)
    Route::prefix('board-posts/{slug}')->name('backoffice.board-posts.')->group(function () {
        Route::get('/', [BoardPostController::class, 'index'])->name('index');
        Route::get('/create', [BoardPostController::class, 'create'])->name('create');
        Route::post('/', [BoardPostController::class, 'store'])->name('store');
        Route::get('/{post}', [BoardPostController::class, 'show'])->name('show');
        Route::get('/{post}/edit', [BoardPostController::class, 'edit'])->name('edit');
        Route::put('/{post}', [BoardPostController::class, 'update'])->name('update');
        Route::delete('/{post}', [BoardPostController::class, 'destroy'])->name('destroy');
        Route::post('/bulk-destroy', [BoardPostController::class, 'bulkDestroy'])->name('bulk_destroy');
    });

    // 게시판 관리
    Route::resource('boards', BoardController::class, [
        'names' => 'backoffice.boards'
    ])->except(['show']); // show는 제외 (게시글 목록과 충돌)

    // 게시판 템플릿 관리
    Route::resource('board-templates', BoardTemplateController::class, [
        'names' => 'backoffice.board-templates',
        'parameters' => ['board-templates' => 'boardTemplate']
    ]);

    // 게시판 템플릿 추가 기능
    Route::post('board-templates/{boardTemplate}/duplicate', [BoardTemplateController::class, 'duplicate'])
        ->name('backoffice.board-templates.duplicate');
    Route::get('board-templates/{boardTemplate}/data', [BoardTemplateController::class, 'getTemplateData'])
        ->name('backoffice.board-templates.data');

    // 게시판 스킨 관리
    Route::resource('board-skins', BoardSkinController::class, [
        'names' => 'backoffice.board-skins',
        'parameters' => ['board-skins' => 'boardSkin']
    ]);

    // 게시판 스킨 템플릿 편집
    Route::prefix('board-skins/{boardSkin}')->name('backoffice.board-skins.')->group(function () {
        Route::get('template', [BoardSkinController::class, 'editTemplate'])
            ->name('edit_template');
        Route::post('template', [BoardSkinController::class, 'updateTemplate'])
            ->name('update_template');
    });

    // 게시글 관리
    Route::resource('posts', BoardPostController::class, [
        'names' => 'backoffice.posts'
    ]);

    // 회원 관리
    Route::resource('users', UserController::class, [
        'names' => 'backoffice.users'
    ]);

    // 회원 관리 (교사/학생)
    Route::resource('members', MemberController::class, [
        'names' => 'backoffice.members'
    ]);
    Route::post('members/bulk-destroy', [MemberController::class, 'bulkDestroy'])
        ->name('backoffice.members.bulk-destroy');
    Route::get('members-export', [MemberController::class, 'export'])
        ->name('backoffice.members.export');

    // 회원 그룹 관리
    Route::resource('member-groups', MemberGroupController::class, [
        'names' => 'backoffice.member-groups'
    ])->except(['show']);
    Route::post('member-groups/bulk-destroy', [MemberGroupController::class, 'bulkDestroy'])
        ->name('backoffice.member-groups.bulk-destroy');
    Route::get('member-groups/search-members', [MemberGroupController::class, 'searchMembers'])
        ->name('backoffice.member-groups.search-members');
    Route::post('member-groups/{member_group}/add-members', [MemberGroupController::class, 'addMembers'])
        ->name('backoffice.member-groups.add-members');
    Route::post('member-groups/{member_group}/remove-member', [MemberGroupController::class, 'removeMember'])
        ->name('backoffice.member-groups.remove-member');

    // 배너 관리
    Route::resource('banners', BannerController::class, [
        'names' => 'backoffice.banners'
    ]);
    Route::post('banners/update-order', [BannerController::class, 'updateOrder'])->name('backoffice.banners.update-order');

    // 팝업 관리
    Route::resource('popups', PopupController::class, [
        'names' => 'backoffice.popups'
    ]);
    Route::post('popups/update-order', [PopupController::class, 'updateOrder'])->name('backoffice.popups.update-order');

    // 프로그램 관리
    Route::get('programs', [ProgramController::class, 'index'])->name('backoffice.programs.index');
    Route::put('programs/{program}', [ProgramController::class, 'update'])->name('backoffice.programs.update');

    // 단체 프로그램 관리
    Route::prefix('group-programs')->name('backoffice.group-programs.')->group(function () {
        Route::get('/', [GroupProgramController::class, 'index'])->name('index');
        Route::get('/create', [GroupProgramController::class, 'create'])->name('create');
        Route::post('/', [GroupProgramController::class, 'store'])->name('store');
        Route::get('/search-programs', [GroupProgramController::class, 'searchPrograms'])->name('search-programs');
        Route::get('/{programReservation}/edit', [GroupProgramController::class, 'edit'])->name('edit');
        Route::put('/{programReservation}', [GroupProgramController::class, 'update'])->name('update');
        Route::delete('/{programReservation}', [GroupProgramController::class, 'destroy'])->name('destroy');
    });

    // 세션 연장
    Route::post('session/extend', [App\Http\Controllers\Backoffice\SessionController::class, 'extend'])
        ->name('backoffice.session.extend');
});

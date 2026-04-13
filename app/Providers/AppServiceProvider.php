<?php

namespace App\Providers;

use App\Models\AdminMenu;
use App\Models\Setting;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request as RequestFacade;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('member-login', function (Request $request) {
            $loginId = strtolower(trim((string) $request->input('login_id', '')));
            $limiterKey = $request->ip() . '|' . $loginId;

            // 스캐너 기준에서 5회 연속 시도 내 차단이 명확히 보이도록 5번째 요청부터 429 반환
            return Limit::perMinutes(10, 4)
                ->by($limiterKey)
                ->response(function () {
                    return response(
                        '로그인 시도 횟수를 초과했습니다. 잠시 후 다시 시도해 주세요.',
                        429
                    );
                });
        });

        View::share('cspNonce', '');

        // HTTPS 강제 (.env의 APP_URL이 https://로 시작하는 경우)
        $applicationUrl = config('app.url');
        if (is_string($applicationUrl) && str_starts_with($applicationUrl, 'https://')) {
            URL::forceScheme('https');
        }

        // Host Header Injection 방지: 링크/리다이렉트가 요청 Host가 아닌 APP_URL 기준으로 생성되도록 고정
        if (is_string($applicationUrl) && $applicationUrl !== '') {
            URL::forceRootUrl($applicationUrl);
        }

        // 백오피스 경로에서 현재 메뉴 정보를 뷰에 공유
        if (RequestFacade::is('backoffice*')) {
            View::composer('*', function ($view) {
                $currentPath = RequestFacade::path();
                $currentMenu = AdminMenu::getCurrentMenu($currentPath);

                // 현재 메뉴가 있으면 타이틀 생성, 없으면 기본 타이틀 사용
                $menuTitle = $currentMenu ? $currentMenu->name : '백오피스';
                $title = "백오피스 - {$menuTitle}";

                $view->with('menuTitle', $menuTitle);
                $view->with('title', $title);
                
                // 사이드바 데이터 추가 (모든 페이지에서 공통 사용)
                $view->with('siteTitle', Setting::getValue('site_title', '관리자'));
                
                // 사용자 권한에 따른 메뉴 필터링
                $user = Auth::user();
                if ($user && $user->isSuperAdmin()) {
                    // 슈퍼 관리자는 모든 메뉴 표시
                    $mainMenus = AdminMenu::getMainMenus();
                } elseif ($user) {
                    // 일반 관리자는 권한 있는 메뉴만 표시
                    $accessibleMenuIds = $user->accessibleMenus()->pluck('admin_menus.id')->toArray();
                    
                    // 부모 메뉴 가져오기 (자식 메뉴는 eager loading하지 않음)
                    $mainMenus = AdminMenu::whereNull('parent_id')
                        ->where('is_active', true)
                        ->orderBy('order')
                        ->get()
                        ->filter(function ($menu) use ($accessibleMenuIds) {
                            // 부모 메뉴 자체에 권한이 있는지 확인
                            $hasParentPermission = in_array($menu->id, $accessibleMenuIds);
                            
                            // 권한이 있는 자식 메뉴만 필터링하여 로드
                            $filteredChildren = AdminMenu::where('parent_id', $menu->id)
                                ->where('is_active', true)
                                ->orderBy('order')
                                ->get()
                                ->filter(function ($child) use ($accessibleMenuIds) {
                                    return in_array($child->id, $accessibleMenuIds);
                                });
                            
                            // 자식 메뉴를 필터링된 것으로 교체
                            $menu->setRelation('children', $filteredChildren);
                            
                            // 부모 메뉴 권한이 있거나, 권한 있는 자식 메뉴가 하나라도 있으면 표시
                            return $hasParentPermission || $filteredChildren->count() > 0;
                        });
                } else {
                    $mainMenus = collect();
                }
                
                $view->with('mainMenus', $mainMenus);
            });
        }

        // 쿼리 로깅 활성화 (디버깅용)
        if (config('app.debug')) {
            DB::listen(function ($query) {
                Log::info(
                    'SQL 쿼리 실행',
                    [
                        'sql' => $query->sql,
                        'bindings' => $query->bindings,
                        'time' => $query->time
                    ]
                );
            });
        }
    }
}

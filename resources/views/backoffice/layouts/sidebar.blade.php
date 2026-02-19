<div class="sidebar_wrap">
	<a href="javascript:void(0);" class="btn_menu_control"><span class="txt">메뉴닫기</span></a>
	<div class="sidebar">
	    <div class="sidebar-header">
			<div class="logo"></div>
	        <h3>{{ $siteTitle }}</h3>
	    </div>
	    <ul class="sidebar-menu">
	        @php
	            $currentPath = request()->path();
	        @endphp
	        @foreach($mainMenus as $menu)
	            @php
	                // 현재 메뉴가 활성화되어야 하는지 확인
	                $isActive = false;
	                $hasActiveChild = false;
	
	                // URL이 있는 메뉴인 경우 직접 확인
	                if($menu->url) {
	                    $menuPath = trim($menu->url, '/');
	                    // URL이 비어있지 않은 경우에만 비교
	                    if(!empty($menuPath)) {
	                        // 정확한 경로 매칭만 사용 (하위 경로는 제외)
	                        $isActive = $currentPath === $menuPath;
	                    }
	                }
	
	                // 서브메뉴가 있는 경우 (메뉴 URL 여부와 관계없이)
	                if($menu->children && $menu->children->count() > 0) {
	                    // 자식 메뉴 중 현재 경로와 일치하는 것이 있는지 확인
	                    foreach($menu->children as $child) {
	                        if($child->url && !empty(trim($child->url, '/'))) {
	                            $childPath = trim($child->url, '/');
	                            // 정확한 경로 매칭만 사용 (하위 경로는 제외)
	                            if($currentPath === $childPath) {
	                                $hasActiveChild = true;
	                                break;
	                            }
	                        }
	                    }
	                }
	
	                // 자식 메뉴가 활성화되어 있다면 부모도 활성화
	                if($hasActiveChild) {
	                    $isActive = true;
	                }
	            @endphp
	            <li class="{{ $isActive ? 'active' : '' }}">
	                @if($menu->url)
	                    <a href="{{ is_string($menu->url) ? url($menu->url) : $menu->url }}">
	                        @if($menu->icon)
	                            <i class="fa {{ $menu->icon }}"></i>
	                        @endif
	                        <span>{{ $menu->name }}</span>
	                    </a>
	                @else
	                    <a href="#" class="has-submenu {{ $isActive ? 'open' : '' }}">
	                        @if($menu->icon)
	                            <i class="fa {{ $menu->icon }}"></i>
	                        @endif
	                        <span>{{ $menu->name }}</span>
	                        <i class="fa fa-angle-down submenu-icon"></i>
	                    </a>
	                    @if($menu->children && $menu->children->count() > 0)
	                        <ul class="sidebar-submenu">
	                            @foreach($menu->children as $child)
	                                @if($child->is_active)
	                                    @php
	                                        $childPath = trim($child->url, '/');
	                                        $isChildActive = !empty($childPath) && $currentPath === $childPath;
	                                    @endphp
	                                    <li class="{{ $isChildActive ? 'active' : '' }}">
	                                        <a href="{{ is_string($child->url) ? url($child->url) : $child->url }}">
	                                            @if($child->icon)
	                                                <i class="fa {{ $child->icon }}"></i>
	                                            @endif
	                                            <span>{{ $child->name }}</span>
	                                        </a>
	                                    </li>
	                                @endif
	                            @endforeach
	                        </ul>
	                    @endif
	                @endif
	            </li>
	        @endforeach
	        
	        @if(isset($visitorStats))
	        <!-- <li class="sidebar-stats-item" style="margin-top: auto; border-top: 1px solid rgba(255,255,255,0.15); padding-top: 12px;">
	            <div class="sidebar-stats" style="padding: 0 20px 16px 20px;">
	                <div class="stats-content" style="display: flex; flex-direction: column; gap: 8px;">
	                    <div class="stat-item" style="display: flex; justify-content: space-between; align-items: center;">
	                        <span class="stat-label" style="color: #adb5bd; font-size: 0.85rem;">오늘</span>
	                        <span class="stat-value" style="color: #fff; font-weight: 600; font-size: 0.9rem;">{{ number_format($visitorStats['today']) }}명</span>
	                    </div>
	                    <div class="stat-item" style="display: flex; justify-content: space-between; align-items: center;">
	                        <span class="stat-label" style="color: #adb5bd; font-size: 0.85rem;">어제</span>
	                        <span class="stat-value" style="color: #fff; font-weight: 600; font-size: 0.9rem;">{{ number_format($visitorStats['yesterday']) }}명</span>
	                    </div>
	                    <div class="stat-item" style="display: flex; justify-content: space-between; align-items: center;">
	                        <span class="stat-label" style="color: #adb5bd; font-size: 0.85rem;">이달</span>
	                        <span class="stat-value" style="color: #fff; font-weight: 600; font-size: 0.9rem;">{{ number_format($visitorStats['this_month']) }}명</span>
	                    </div>
	                    <div class="stat-item" style="display: flex; justify-content: space-between; align-items: center;">
	                        <span class="stat-label" style="color: #adb5bd; font-size: 0.85rem;">전체</span>
	                        <span class="stat-value" style="color: #fff; font-weight: 600; font-size: 0.9rem;">{{ number_format($visitorStats['total']) }}명</span>
	                    </div>
	                </div>
	            </div>
	        </li> -->
	        @endif
	    </ul>
	</div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
	const btn = document.querySelector(".btn_menu_control");
	const wrap = document.querySelector(".dashboard-container");
	if(!btn || !wrap) return;
	btn.addEventListener("click", function (e) {
		e.preventDefault();
		wrap.classList.toggle("close_set");
		setTimeout(() => {
			const txt = btn.querySelector(".txt");
			if(txt){
				txt.textContent = wrap.classList.contains("close_set") ? "메뉴열기" : "메뉴닫기";
			}
		}, 0);
	});
});
</script>

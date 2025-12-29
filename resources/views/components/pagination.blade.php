@props(['paginator'])

<div class="board-pagination">
    <nav aria-label="페이지 네비게이션">
        <ul class="pagination">
            {{-- 첫 페이지로 이동 --}}
            @php
                $pageQuery = fn ($page) => request()->fullUrlWithQuery(['page' => $page]);
            @endphp

            @if ($paginator->onFirstPage())
                <li class="page-item disabled">
                    <span class="page-link">
                        <i class="fas fa-angle-double-left"></i>
                    </span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $pageQuery(1) }}" title="첫 페이지로">
                        <i class="fas fa-angle-double-left"></i>
                    </a>
                </li>
            @endif

            {{-- 10단위 블록 계산 --}}
            @php
                $currentPage = $paginator->currentPage();
                $lastPage = $paginator->lastPage();
                
                // 현재 페이지가 속한 블록의 시작 페이지 계산 (10단위)
                // 예: 페이지 15 → 블록 시작 11, 페이지 3 → 블록 시작 1
                $blockStart = floor(($currentPage - 1) / 10) * 10 + 1;
                
                // 블록의 끝 페이지 계산 (최대 10개, 마지막 페이지 초과하지 않음)
                $blockEnd = min($blockStart + 9, $lastPage);
                
                // 이전 블록의 첫 페이지
                $prevBlockStart = max(1, $blockStart - 10);
                
                // 다음 블록의 첫 페이지
                $nextBlockStart = $blockStart + 10;
                $hasNextBlock = $nextBlockStart <= $lastPage;
            @endphp

            {{-- 이전 블록 링크 --}}
            @if ($blockStart <= 1)
                <li class="page-item disabled">
                    <span class="page-link">
                        <i class="fas fa-chevron-left"></i>
                    </span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $pageQuery($prevBlockStart) }}" rel="prev" title="이전 블록">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                </li>
            @endif

            {{-- 페이지 번호들 (10단위 블록 고정) --}}
            @for ($page = $blockStart; $page <= $blockEnd; $page++)
                @if ($page == $paginator->currentPage())
                    <li class="page-item active">
                        <span class="page-link">{{ $page }}</span>
                    </li>
                @else
                    <li class="page-item">
                        <a class="page-link" href="{{ $pageQuery($page) }}">{{ $page }}</a>
                    </li>
                @endif
            @endfor

            {{-- 다음 블록 링크 --}}
            @if ($hasNextBlock)
                <li class="page-item">
                    <a class="page-link" href="{{ $pageQuery($nextBlockStart) }}" rel="next" title="다음 블록">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </li>
            @else
                <li class="page-item disabled">
                    <span class="page-link">
                        <i class="fas fa-chevron-right"></i>
                    </span>
                </li>
            @endif

            {{-- 마지막 페이지로 이동 --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $pageQuery($paginator->lastPage()) }}" title="마지막 페이지로">
                        <i class="fas fa-angle-double-right"></i>
                    </a>
                </li>
            @else
                <li class="page-item disabled">
                    <span class="page-link">
                        <i class="fas fa-angle-double-right"></i>
                    </span>
                </li>
            @endif
        </ul>
    </nav>   
   
</div>

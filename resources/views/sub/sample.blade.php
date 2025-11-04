@extends('layouts.app')

@section('content')
<main class="flex max-w-[335px] w-full flex-col-reverse lg:max-w-4xl lg:flex-row">
    <div class="text-[13px] leading-[20px] flex-1 p-6 pb-12 lg:p-20 bg-white dark:bg-[#161615] dark:text-[#EDEDEC] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-bl-lg rounded-br-lg lg:rounded-tl-lg lg:rounded-br-none">
        <h1 class="mb-4 text-2xl font-bold">서브페이지 예시 - 샘플페이지</h1>
        <p class="mb-4 text-[#706f6c] dark:text-[#A1A09A]">이것은 퍼블리싱 작업을 위한 서브페이지 예시입니다.</p>
        
        <div class="mb-6">
            <h2 class="text-lg font-semibold mb-2">페이지 구성 예시</h2>
            <ul class="list-disc list-inside space-y-2 text-[#706f6c] dark:text-[#A1A09A]">
                <li>헤더 영역</li>
                <li>콘텐츠 영역</li>
                <li>푸터 영역</li>
            </ul>
        </div>
        
        <div class="mb-6">
            <h2 class="text-lg font-semibold mb-2">작업 가능 항목</h2>
            <div class="p-4 bg-gray-50 dark:bg-[#0a0a0a] rounded-lg">
                <p class="mb-2">HTML 구조, CSS 스타일, JavaScript 동작을 자유롭게 수정하실 수 있습니다.</p>
                <p class="mb-2"><strong>URL:</strong> <code class="bg-gray-200 dark:bg-[#3E3E3A] px-2 py-1 rounded">http://localhost:8000/sub/sample</code></p>
                <p class="mb-2"><strong>컨트롤러:</strong> <code class="bg-gray-200 dark:bg-[#3E3E3A] px-2 py-1 rounded">SubController@sample</code></p>
                <p><strong>뷰 파일:</strong> <code class="bg-gray-200 dark:bg-[#3E3E3A] px-2 py-1 rounded">resources/views/sub/sample.blade.php</code></p>
            </div>
        </div>
        
        <div>
            <a href="{{ route('home') }}" class="inline-flex items-center space-x-2 px-4 py-2 bg-[#f53003] dark:bg-[#FF4433] text-white rounded-lg hover:opacity-90 transition">
                <span>메인으로 돌아가기</span>
            </a>
        </div>
    </div>
    <div class="w-[448px] h-14.5 lg:w-[438px] lg:h-14 bg-[#1b1b18] dark:bg-[#0a0a0a] rounded-t-lg lg:rounded-t-none lg:rounded-tl-lg lg:rounded-r-lg lg:rounded-br-none lg:rounded-bl-lg border border-[#dbdbd7] dark:border-[#3E3E3A] lg:border-l-0 lg:border-t-0 lg:border-r lg:border-b-0 lg:border-l lg:border-t lg:border-r-0 lg:border-b-0 overflow-hidden filter drop-shadow-[0px_0px_1px_0px_rgba(0,0,0,0.03),0px_1px_2px_0px_rgba(0,0,0,0.06)] lg:drop-shadow-none"></div>
</main>
@endsection


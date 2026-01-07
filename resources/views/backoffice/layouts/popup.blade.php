<!DOCTYPE html>
<html lang="ko">
<head>
    @include('backoffice.layouts.header', ['title' => '팝업'])
    <link rel="stylesheet" href="{{ asset('css/backoffice/member-groups.css') }}">
    <link rel="stylesheet" href="{{ asset('css/backoffice/popups.css') }}">
    @yield('styles')
    <style>
        /* 시/도 셀렉박스 한 줄 배치 */
        .modal-form-inline.city-selects {
            display: flex;
            gap: 8px;
            align-items: center;
        }
        .modal-form-inline.city-selects .modal-form-control {
            flex: 1;
            min-width: 0;
        }
        body {
            margin: 0;
            padding: 0;
            background: #fff;
        }
        .popup-container {
            width: 100%;
            height: 100vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            background: #fff;
        }
        .popup-header {
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-shrink: 0;
        }
        .popup-header h5 {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
        }
        .popup-close-btn {
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: #6c757d;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .popup-close-btn:hover {
            color: #495057;
        }
        .popup-body {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            background: #fff;
        }
        @media (max-width: 768px) {
            .popup-header {
                padding: 12px 15px;
            }
            .popup-header h5 {
                font-size: 16px;
            }
            .popup-body {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="popup-container">
        <div class="popup-header">
            <h5>@yield('popup-title', '팝업')</h5>
            <button type="button" class="popup-close-btn" onclick="window.close()" title="닫기">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="popup-body">
            @yield('content')
        </div>
    </div>
    @yield('scripts')
</body>
</html>


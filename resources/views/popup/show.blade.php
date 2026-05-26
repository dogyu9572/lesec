<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $popup->title }}</title>
    <style nonce="{{ $cspNonce }}">
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f5f5;
            padding: 0;
            margin: 0;
        }
        
        .popup-container {
            width: 100%;
            height: 100vh;
            margin: 0;
            background: #fff;
            border-radius: 0;
            box-shadow: none;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        
        .popup-body {
            flex: 1;
            padding: 0;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow-y: auto;
            min-height: 0;
        }
        
        /* HTML 콘텐츠일 때는 위에서 시작 */
        .popup-body.popup-body-html {
            align-items: flex-start;
            padding: 20px;
        }

        .popup-body.popup-body-html.editor-content {
            display: block;
            text-align: left;
            line-height: 1.6;
            overflow-x: auto;
        }

        .popup-body.popup-body-html.editor-content p {
            margin: 0 0 16px;
        }

        .popup-body.popup-body-html.editor-content table {
            width: 100%;
            border-collapse: collapse;
            margin: 16px 0;
            table-layout: auto;
        }

        .popup-body.popup-body-html.editor-content th,
        .popup-body.popup-body-html.editor-content td {
            border: 1px solid #ddd;
            padding: 10px 12px;
            line-height: 1.5;
            vertical-align: middle;
        }

        .popup-body.popup-body-html.editor-content th {
            background: #f7f8f9;
            font-weight: 700;
        }

        .popup-body.popup-body-html.editor-content a {
            display: inline;
            line-height: inherit;
            color: #0066cc;
            text-decoration: underline;
        }

        .popup-body.popup-body-html.editor-content img {
            max-width: 100%;
            width: auto;
            height: auto;
            object-fit: contain;
        }

        .popup-body.popup-body-html.editor-content ul,
        .popup-body.popup-body-html.editor-content ol {
            margin: 16px 0;
            padding-left: 40px;
        }
        
        .popup-body img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
        }
        
        .popup-body a {
            display: block;
            line-height: 0;
        }
        
        .popup-footer {
            background: #f8f9fa;
            padding: 10px 20px;
            border-top: 1px solid #dee2e6;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-shrink: 0;
        }
        
        .popup-today-label {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            font-size: 14px;
            color: #666;
            user-select: none;
        }
        
        .popup-today-close {
            margin: 0;
        }
        
        .popup-close-btn {
            background: #6c757d;
            color: white;
            border: 1px solid #6c757d;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
            transition: all 0.2s;
        }
        
        .popup-close-btn:hover {
            background: #5a6268;
            border-color: #5a6268;
        }
        
        @media (max-width: 768px) {
            .popup-footer {
                padding: 8px 15px;
            }
        }
    </style>
</head>
<body>
    <div class="popup-container">
        <div class="popup-body {{ $popup->popup_type === 'html' ? 'popup-body-html editor-content' : '' }}">
            @if($popup->popup_type === 'image' && $popup->popup_image)
                @if($popup->url)
                    <a href="{{ $popup->url }}" target="{{ $popup->url_target }}">
                        <img src="{{ asset('storage/' . $popup->popup_image) }}" alt="{{ $popup->title }}">
                    </a>
                @else
                    <img src="{{ asset('storage/' . $popup->popup_image) }}" alt="{{ $popup->title }}">
                @endif
            @elseif($popup->popup_type === 'html' && $popup->popup_content)
                {!! $popup->popup_content !!}
            @endif
        </div>
        
        <div class="popup-footer">
            <label class="popup-today-label">
                <input type="checkbox" class="popup-today-close" id="todayClose">
                1일 동안 보지 않음
            </label>
            <button type="button" class="popup-close-btn" id="popupCloseBtn">닫기</button>
        </div>
    </div>

    <script nonce="{{ $cspNonce }}">
        function closePopup() {
            const todayCloseEl = document.getElementById('todayClose');
            const todayClose = todayCloseEl ? todayCloseEl.checked : false;
            if (todayClose) {
                const expires = new Date();
                expires.setHours(23, 59, 59, 999);
                document.cookie = 'popup_hide_{{ $popup->id }}=true; expires=' + expires.toUTCString() + '; path=/';
            }
            window.close();
        }

        document.getElementById('popupCloseBtn').addEventListener('click', closePopup);
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closePopup();
            }
        });
    </script>
</body>
</html>

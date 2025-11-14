@php
    $modalId = $modalId ?? 'program-search-modal';
    $formId = $formId ?? 'program-search-form';
    $searchAction = $searchAction ?? '';
    $educationTypes = $educationTypes ?? [
        'middle_semester' => '중등학기',
        'middle_vacation' => '중등방학',
        'high_semester' => '고등학기',
        'high_vacation' => '고등방학',
        'special' => '특별프로그램',
    ];
    $mode = $mode ?? 'reservation'; // reservation | name
    $modalTitle = $modalTitle ?? '프로그램명 검색';
    $showDirectInputNotice = $showDirectInputNotice ?? ($mode === 'name');
    $confirmButtonLabel = $confirmButtonLabel ?? '확인';
    $programInputId = $programInputId ?? 'program_name';
    $showFooter = $showFooter ?? ($mode === 'reservation');
@endphp

<div id="{{ $modalId }}"
     class="category-modal"
     style="display: none;"
     data-program-search-mode="{{ $mode }}"
     @if(!empty($programInputId ?? null)) data-program-input-id="{{ $programInputId }}" @endif
     @if(!empty($searchAction)) data-program-search-url="{{ $searchAction }}" @endif>
    <div class="category-modal-overlay" onclick="closeProgramSearchModal()"></div>
    <div class="category-modal-content" style="max-width: {{ $mode === 'reservation' ? '900px' : '800px' }};">
        <div class="category-modal-header">
            <h5>{{ $modalTitle }}</h5>
            <button type="button" class="category-modal-close" onclick="closeProgramSearchModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="category-modal-body">
            <div class="program-search-filter">
                <form id="{{ $formId }}" method="GET" action="{{ $searchAction }}">
                    <div class="modal-form-row">
                        <div class="modal-form-col">
                            <div class="modal-form-group">
                                <label for="popup_education_type" class="modal-form-label">교육유형</label>
                                <select id="popup_education_type" name="education_type" class="modal-form-control">
                                    <option value="">전체</option>
                                    @foreach($educationTypes as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    <div class="modal-form-col">
                            <div class="modal-form-group">
                                <label for="popup_program_keyword" class="modal-form-label">프로그램명</label>
                                <input type="text" id="popup_program_keyword" name="search_keyword" class="modal-form-control" placeholder="프로그램명 입력">
                            </div>
                        </div>
                        <div class="modal-form-col search-button-col">
                            <div class="modal-form-group">
                                <label class="modal-form-label search-button-label">버튼</label>
                                <button type="button" id="popup-program-search-btn" class="btn btn-primary">검색</button>
                            </div>
                        </div>
                    </div>
                </form>
                @if($showDirectInputNotice)
                    <div style="margin-top: 15px; padding: 10px; background-color: #f8f9fa; border-radius: 4px;" class="program-direct-input">
                        <label for="popup_direct_program_name" style="display:block; font-size: 14px; color: #666; margin-bottom: 6px;">프로그램명이 목록에 없으면 직접 입력해 주세요.</label>
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <input type="text" id="popup_direct_program_name" class="modal-form-control" placeholder="프로그램명 직접 입력">
                            <button type="button" id="popup-confirm-btn" class="btn btn-secondary btn-sm">확인</button>
                        </div>
                    </div>
                @endif
            </div>
            <div class="table-responsive">
                <table class="board-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>프로그램명</th>
                            @if($mode === 'reservation')
                                <th>교육유형</th>
                                <th>교육일</th>
                                <th>참가비</th>
                            @endif
                            <th style="width: 80px;">선택</th>
                        </tr>
                    </thead>
                    <tbody id="popup-program-list-body">
                        <tr>
                            <td colspan="{{ $mode === 'reservation' ? 6 : 3 }}" class="text-center">검색 조건을 입력해주세요.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        @if($mode === 'reservation' && $showFooter)
            <div class="category-modal-footer">
                <button type="button" id="popup-program-confirm" class="btn btn-primary" disabled>{{ $confirmButtonLabel }}</button>
            </div>
        @endif
        <div id="popup-program-pagination" class="board-pagination" style="margin-top: 15px;"></div>
    </div>
</div>

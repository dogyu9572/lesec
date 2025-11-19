@php
    $modalId = $modalId ?? 'bulk-upload-modal';
    $formId = $formId ?? 'bulk-upload-search-form';
    $searchAction = $searchAction ?? route('backoffice.individual-applications.search-programs');
    $educationTypes = $educationTypes ?? \App\Models\IndividualApplication::EDUCATION_TYPE_LABELS;
@endphp

<div id="{{ $modalId }}"
     class="category-modal"
     style="display: none;"
     data-search-url="{{ $searchAction }}">
    <div class="category-modal-overlay" onclick="closeBulkUploadModal()"></div>
    <div class="category-modal-content" style="max-width: 900px;">
        <div class="category-modal-header">
            <h5>일괄 업로드</h5>
            <button type="button" class="category-modal-close" onclick="closeBulkUploadModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="category-modal-body">
            <div class="program-search-filter">
                <form id="{{ $formId }}" method="GET" action="{{ $searchAction }}">
                    <div class="modal-form-row">
                        <div class="modal-form-col">
                            <div class="modal-form-group">
                                <label for="bulk_upload_education_type" class="modal-form-label">교육유형</label>
                                <select id="bulk_upload_education_type" name="education_type" class="modal-form-control">
                                    <option value="">전체</option>
                                    @foreach($educationTypes as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="modal-form-col">
                            <div class="modal-form-group">
                                <label for="bulk_upload_program_keyword" class="modal-form-label">프로그램명</label>
                                <input type="text" id="bulk_upload_program_keyword" name="search_keyword" class="modal-form-control" placeholder="프로그램명 입력">
                            </div>
                        </div>
                        <div class="modal-form-col search-button-col">
                            <div class="modal-form-group">
                                <label class="modal-form-label search-button-label">버튼</label>
                                <button type="button" id="bulk-upload-search-btn" class="btn btn-primary btn-sm">검색</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="category-modal-footer category-modal-footer--inline">
                <a href="{{ route('backoffice.individual-applications.sample') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-download"></i> 양식 다운로드
                </a>
            </div>
            <div class="table-responsive">
                <table class="board-table">
                    <thead>
                        <tr>
                            <th>N</th>
                            <th>교육유형</th>
                            <th>참가일정</th>
                            <th>프로그램명</th>
                            <th style="width: 80px;">선택</th>
                        </tr>
                    </thead>
                    <tbody id="bulk-upload-program-list-body">
                        <tr>
                            <td colspan="5" class="text-center">검색 조건을 입력해주세요.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div id="bulk-upload-pagination" class="board-pagination" style="margin-top: 15px;"></div>
            
            <div id="bulk-upload-section" style="display: none; margin-top: 20px; padding: 15px; background-color: #f8f9fa; border-radius: 4px;">
                <div style="margin-bottom: 10px;">
                    <strong>선택한 프로그램:</strong>
                    <span id="selected-program-info"></span>
                </div>
                <div style="display: flex; gap: 10px; align-items: center;">
                    <label style="margin: 0;">일괄 업로드</label>
                    <input type="file" id="bulk-upload-file" accept=".csv,.xlsx,.xls" style="display: none;">
                    <button type="button" id="bulk-upload-file-select-btn" class="btn btn-secondary">파일 선택</button>
                </div>
            </div>
        </div>
       
    </div>
</div>


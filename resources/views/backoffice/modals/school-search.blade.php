<div id="school-search-modal" class="category-modal" style="display: none;">
    <div class="category-modal-overlay"></div>
    <div class="category-modal-content school-search-modal">
        <div class="category-modal-header">
            <h5>학교 검색</h5>
            <button type="button" class="category-modal-close">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="category-modal-body">
            <form id="school-search-form" data-url="{{ route('member.schools.search') }}">
                <div class="modal-form-group">
                    <label class="modal-form-label">시/도<span class="required">*</span></label>
                    <div class="modal-form-inline city-selects">
                        <select id="search_city" name="city" class="modal-form-control">
                            <option value="">시/도 선택</option>
                        </select>
                        <select id="search_district" name="district" class="modal-form-control">
                            <option value="">시/군/구 선택</option>
                        </select>
                    </div>
                </div>
                <div class="modal-form-group">
                    <label class="modal-form-label">학교급<span class="required">*</span></label>
                    <select id="search_school_level" name="school_level" class="modal-form-control">
                        <option value="">학교급 선택</option>
                    </select>
                </div>
                <div class="modal-form-group">
                    <label class="modal-form-label">학교명<span class="required">*</span></label>
                    <div class="modal-form-inline keyword-inline">
                        <input type="text" id="search_keyword" name="keyword" class="modal-form-control" placeholder="학교명을 입력하세요">
                        <button type="submit" class="btn btn-primary btn-search">검색</button>
                    </div>
                    <p class="modal-description">학교명이 목록에 없을 경우, 직접 입력하여 등록해주세요.</p>
                </div>
            </form>
            <div class="table-responsive mt-3">
                <table class="board-table school-search-table">
                    <thead>
                        <tr>
                            <th class="school-select-cell">선택</th>
                            <th>시/도</th>
                            <th>학교명</th>
                        </tr>
                    </thead>
                    <tbody id="school-search-results">
                        <tr>
                            <td colspan="3" class="text-center">검색 조건을 입력하고 검색 버튼을 클릭하세요.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="category-modal-footer">
            <button type="button" id="school-select-confirm" class="btn btn-primary" disabled>확인</button>
            <button type="button" class="btn btn-secondary school-search-cancel">취소</button>
        </div>
    </div>
</div>

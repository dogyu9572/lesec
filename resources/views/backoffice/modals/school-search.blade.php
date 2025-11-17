<div id="school-search-modal" class="category-modal" style="display: none;">
    <div class="category-modal-overlay" onclick="closeSchoolSearchModal()"></div>
    <div class="category-modal-content school-search-modal" style="max-width: 900px;">
        <div class="category-modal-header">
            <h5>학교 검색</h5>
            <button type="button" class="category-modal-close" onclick="closeSchoolSearchModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="category-modal-body">
            <div class="program-search-filter">
                <form id="school-search-form" data-url="{{ route('member.schools.search') }}">
                    <div class="modal-form-row">
                        <div class="modal-form-col">
                            <div class="modal-form-group">
                                <label class="modal-form-label">시/도</label>
                                <div class="modal-form-inline city-selects">
                                    <select id="search_city" name="city" class="modal-form-control">
                                        <option value="">시/도 선택</option>
                                    </select>
                                    <select id="search_district" name="district" class="modal-form-control">
                                        <option value="">시/군/구 선택</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="modal-form-col">
                            <div class="modal-form-group">
                                <label class="modal-form-label">학교급</label>
                                <select id="search_school_level" name="school_level" class="modal-form-control">
                                    <option value="">학교급 선택</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-form-col">
                            <div class="modal-form-group">
                                <label class="modal-form-label">학교명</label>
                                <input type="text" id="search_keyword" name="keyword" class="modal-form-control" placeholder="학교명을 입력하세요">
                            </div>
                        </div>
                        <div class="modal-form-col search-button-col">
                            <div class="modal-form-group">
                                <label class="modal-form-label search-button-label">버튼</label>
                                <button type="submit" class="btn btn-primary btn-search">검색</button>
                            </div>
                        </div>
                    </div>
                </form>
                <div class="program-direct-input" style="margin-top: 15px; padding: 10px; background-color: #f8f9fa; border-radius: 4px;">
                    <label for="school_direct_input" style="display:block; font-size: 14px; color: #666; margin-bottom: 6px;">학교명이 목록에 없으면 직접 입력해 주세요.</label>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <input type="text" id="school_direct_input" class="modal-form-control" placeholder="학교명 직접 입력">
                        <button type="button" id="school-direct-confirm" class="btn btn-secondary btn-sm">확인</button>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="board-table school-search-table">
                    <thead>
                        <tr>
                            <th style="width: 60px;">No</th>
                            <th>학교명</th>
                            <th>지역</th>
                            <th style="width: 100px;">선택</th>
                        </tr>
                    </thead>
                    <tbody id="school-search-results">
                        <tr>
                            <td colspan="4" class="text-center">검색 조건을 입력하고 검색 버튼을 클릭하세요.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div id="school-search-pagination" class="board-pagination" style="margin-top: 15px;"></div>
        </div>
    </div>
</div>

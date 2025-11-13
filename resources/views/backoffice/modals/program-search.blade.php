<div id="program-search-modal" class="category-modal" style="display: none;">
    <div class="category-modal-overlay" onclick="closeProgramSearchModal()"></div>
    <div class="category-modal-content" style="max-width: 900px;">
        <div class="category-modal-header">
            <h5>프로그램 검색</h5>
            <button type="button" class="category-modal-close" onclick="closeProgramSearchModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="category-modal-body">
            <div class="program-search-filter">
                <form id="program-search-form">
                    <div class="modal-form-row">
                        <div class="modal-form-col">
                            <div class="modal-form-group">
                                <label for="popup_education_type" class="modal-form-label">교육유형</label>
                                <select id="popup_education_type" name="education_type" class="modal-form-control">
                                    <option value="">전체</option>
                                    <option value="middle_semester">중등학기</option>
                                    <option value="middle_vacation">중등방학</option>
                                    <option value="high_semester">고등학기</option>
                                    <option value="high_vacation">고등방학</option>
                                    <option value="special">특별프로그램</option>
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
            </div>
            <div class="table-responsive">
                <table class="board-table">
                    <thead>
                        <tr>
                            <th style="width: 40px;">선택</th>
                            <th>No</th>
                            <th>교육유형</th>
                            <th>프로그램명</th>
                            <th>교육일</th>
                            <th>참가비</th>
                        </tr>
                    </thead>
                    <tbody id="popup-program-list-body">
                        <tr>
                            <td colspan="6" class="text-center">검색 조건을 입력해주세요.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div id="popup-program-pagination" class="pagination-container"></div>
        </div>
        <div class="category-modal-footer">
            <button type="button" id="popup-program-confirm" class="btn btn-primary" disabled>확인</button>
            <button type="button" class="btn btn-secondary program-search-cancel" onclick="closeProgramSearchModal()">취소</button>
        </div>
    </div>
</div>

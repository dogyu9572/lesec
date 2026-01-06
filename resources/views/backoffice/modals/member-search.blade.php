@php
    $formAction = $formAction ?? null;
@endphp

<div id="member-search-modal" class="category-modal" style="display: none;">
    <div class="category-modal-overlay" onclick="closeMemberSearchModal()"></div>
    <div class="category-modal-content" style="max-width: 900px;">
        <div class="category-modal-header">
            <h5>회원 검색</h5>
            <button type="button" class="category-modal-close" onclick="closeMemberSearchModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="category-modal-body">
            <div class="member-search-filter">
                <form id="member-search-form" @if($formAction) action="{{ $formAction }}" method="GET" @endif>
                    <div class="modal-form-row">
                        <div class="modal-form-col">
                            <div class="modal-form-group">
                                <label for="popup_search_type" class="modal-form-label">검색</label>
                                <select id="popup_search_type" name="search_type" class="modal-form-control">
                                    <option value="all">전체</option>
                                    <option value="login_id">아이디</option>
                                    <option value="name">이름</option>
                                    <option value="email">이메일</option>
                                    <option value="school_name">학교</option>
                                    <option value="contact">연락처</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-form-col">
                            <div class="modal-form-group">
                                <label for="popup_search_keyword" class="modal-form-label">검색어</label>
                                <input type="text" id="popup_search_keyword" name="search_keyword" class="modal-form-control" placeholder="검색어 입력">
                            </div>
                        </div>
                        <div class="modal-form-col search-button-col">
                            <div class="modal-form-group">
                                <label class="modal-form-label search-button-label">버튼</label>
                                <div style="display: flex; gap: 8px;">
                                <button type="button" id="popup-search-btn" class="btn btn-primary">검색</button>
                                    <button type="button" id="popup-reset-btn" class="btn btn-secondary">뒤로가기</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="table-responsive">
                <table class="board-table">
                    <thead>
                            <tr>
                                <th style="width: 40px;">
                                    <input type="checkbox" id="popup-select-all">
                                </th>
                                <th>No</th>
                                <th>ID</th>
                                <th>이름</th>
                                <th>학교</th>
                                <th>이메일</th>
                            <th>연락처</th>
                            </tr>
                    </thead>
                    <tbody id="popup-member-list-body">
                        <tr>
                            <td colspan="7" class="text-center">검색어를 입력하거나 필터를 선택해주세요.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div id="popup-pagination" class="pagination-container"></div>
        </div>
            <div class="category-modal-footer">
            <button type="button" id="popup-add-btn" class="btn btn-primary" disabled>선택 완료</button>
                <button type="button" class="btn btn-secondary member-search-cancel" onclick="closeMemberSearchModal()">취소</button>
            </div>
    </div>
</div>

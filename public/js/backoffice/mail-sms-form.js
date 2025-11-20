document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('member-search-modal');
    const openBtn = document.getElementById('openMemberSearchBtn');
    const searchBtn = document.getElementById('popup-search-btn');
    const confirmBtn = document.getElementById('popup-add-btn');
    const selectAllCheckbox = document.getElementById('popup-select-all');
    const memberTypeSelect = document.getElementById('popup_member_type');
    const searchInput = document.getElementById('popup_search_term');
    const memberGroupSelect = document.getElementById('member_group_id');
    const memberListBody = document.getElementById('popup-member-list-body');
    const paginationContainer = document.getElementById('popup-pagination');
    const selectedMembersBody = document.getElementById('selectedMembersBody');

    if (!modal || !openBtn || !memberListBody || !selectedMembersBody) {
        return;
    }

    const config = window.mailSmsFormConfig || {};
    const selectedMemberIds = new Set();

    /**
     * 초기 선택 상태 구성
     */
    Array.from(selectedMembersBody.querySelectorAll('input[name="member_ids[]"]')).forEach((input) => {
        const value = parseInt(input.value, 10);
        if (value) {
            selectedMemberIds.add(value);
        }
    });

    /**
     * 모달 열기
     */
    function openMemberSearchModal() {
        modal.style.display = 'flex';
        if (!memberListBody.dataset.initialized) {
            searchMembers(1);
            memberListBody.dataset.initialized = 'true';
        }
    }

    /**
     * 모달 닫기
     */
    function closeMemberSearchModal() {
        modal.style.display = 'none';
        clearModalSelection();
    }

    window.openMemberSearchModal = openMemberSearchModal;
    window.closeMemberSearchModal = closeMemberSearchModal;

    /**
     * 모달 체크박스 상태 초기화
     */
    function clearModalSelection() {
        selectAllCheckbox.checked = false;
        const checkboxes = memberListBody.querySelectorAll('.popup-member-checkbox');
        checkboxes.forEach((checkbox) => (checkbox.checked = false));
        toggleConfirmButton();
    }

    /**
     * 선택된 회원 출력
     */
    function renderSelectedMembersEmptyRow() {
        if (selectedMemberIds.size > 0) {
            const emptyRow = selectedMembersBody.querySelector('.selected-member-empty');
            if (emptyRow) {
                emptyRow.remove();
            }
            return;
        }

        if (!selectedMembersBody.querySelector('.selected-member-empty')) {
            const row = document.createElement('tr');
            row.classList.add('selected-member-empty');
            row.innerHTML = '<td colspan="4" class="text-center text-muted">선택된 회원이 없습니다.</td>';
            selectedMembersBody.appendChild(row);
        }
    }

    /**
     * 회원 목록 조회
     */
    function searchMembers(page = 1) {
        const params = new URLSearchParams();
        const memberType = memberTypeSelect?.value || 'all';
        const keyword = searchInput?.value || '';
        const groupId = memberGroupSelect?.value || '';

        params.append('member_type', memberType);
        params.append('search_term', keyword);
        if (groupId) {
            params.append('member_group_id', groupId);
        }
        params.append('per_page', '10');
        params.append('page', page.toString());

        fetch(`${config.searchUrl}?${params.toString()}`, {
            headers: {
                'X-CSRF-TOKEN': config.csrfToken || '',
                Accept: 'application/json',
            },
        })
            .then((response) => {
                if (!response.ok) {
                    throw new Error('회원 검색 중 오류가 발생했습니다.');
                }
                return response.json();
            })
            .then((data) => {
                renderMemberList(data.members || []);
                renderPagination(data.pagination || { current_page: 1, last_page: 1 });
            })
            .catch((error) => {
                console.error(error);
                alert(error.message);
                renderMemberList([]);
                renderPagination({ current_page: 1, last_page: 1 });
            });
    }

    /**
     * 모달 회원 목록 렌더링
     */
    function renderMemberList(members) {
        if (!Array.isArray(members) || members.length === 0) {
            memberListBody.innerHTML =
                '<tr><td colspan="6" class="text-center text-muted">검색 결과가 없습니다.</td></tr>';
            return;
        }

        const rows = members
            .map((member, index) => {
                const number = index + 1;
                const disabled = selectedMemberIds.has(member.id) ? 'disabled' : '';
                return `
                    <tr>
                        <td>
                            <input type="checkbox"
                                class="popup-member-checkbox"
                                value="${member.id}"
                                data-name="${member.name || ''}"
                                data-email="${member.email || ''}"
                                data-contact="${member.contact || ''}"
                                ${disabled}>
                        </td>
                        <td>${number}</td>
                        <td>${member.login_id || '-'}</td>
                        <td>${member.name || '-'}</td>
                        <td>${member.school_name || '-'}</td>
                        <td>${member.email || '-'}</td>
                    </tr>
                `;
            })
            .join('');

        memberListBody.innerHTML = rows;
        memberListBody.querySelectorAll('.popup-member-checkbox').forEach((checkbox) => {
            checkbox.addEventListener('change', toggleConfirmButton);
        });

        toggleConfirmButton();
    }

    /**
     * 페이지네이션 렌더링
     */
    function renderPagination(pagination) {
        const lastPage = pagination.last_page || 1;
        const currentPage = pagination.current_page || 1;

        if (lastPage <= 1) {
            paginationContainer.innerHTML = '';
            return;
        }

        let html = '<div class="pagination">';

        if (currentPage > 1) {
            html += `<a href="#" data-page="${currentPage - 1}">&lt;</a>`;
        } else {
            html += '<span class="disabled">&lt;</span>';
        }

        for (let page = 1; page <= lastPage; page += 1) {
            if (page === currentPage) {
                html += `<span class="active">${page}</span>`;
            } else {
                html += `<a href="#" data-page="${page}">${page}</a>`;
            }
        }

        if (currentPage < lastPage) {
            html += `<a href="#" data-page="${currentPage + 1}">&gt;</a>`;
        } else {
            html += '<span class="disabled">&gt;</span>';
        }

        html += '</div>';
        paginationContainer.innerHTML = html;

        paginationContainer.querySelectorAll('a[data-page]').forEach((link) => {
            link.addEventListener('click', (event) => {
                event.preventDefault();
                const page = parseInt(event.currentTarget.dataset.page, 10);
                searchMembers(page);
            });
        });
    }

    /**
     * 선택 회원 추가
     */
    function addSelectedMembers() {
        const checkboxes = memberListBody.querySelectorAll('.popup-member-checkbox:checked');
        if (!checkboxes.length) {
            alert('추가할 회원을 선택해 주세요.');
            return;
        }

        checkboxes.forEach((checkbox) => {
            const memberId = parseInt(checkbox.value, 10);
            if (!memberId || selectedMemberIds.has(memberId)) {
                return;
            }

            selectedMemberIds.add(memberId);
            const row = document.createElement('tr');
            row.dataset.memberId = memberId.toString();
            row.innerHTML = `
                <td>${checkbox.dataset.name || '-'}</td>
                <td>${checkbox.dataset.email || '-'}</td>
                <td>${checkbox.dataset.contact || '-'}</td>
                <td>
                    <button type="button" class="btn btn-outline-danger btn-sm selected-member-remove" data-member-id="${memberId}">삭제</button>
                    <input type="hidden" name="member_ids[]" value="${memberId}">
                </td>
            `;
            selectedMembersBody.appendChild(row);
        });

        renderSelectedMembersEmptyRow();
        closeMemberSearchModal();
    }

    /**
     * 선택 회원 삭제
     */
    selectedMembersBody.addEventListener('click', (event) => {
        const target = event.target;
        if (!target.classList.contains('selected-member-remove')) {
            return;
        }

        const memberId = parseInt(target.dataset.memberId, 10);
        if (!memberId) {
            return;
        }

        selectedMemberIds.delete(memberId);
        const row = selectedMembersBody.querySelector(`tr[data-member-id="${memberId}"]`);
        if (row) {
            row.remove();
        }
        renderSelectedMembersEmptyRow();
    });

    /**
     * 확인 버튼 상태 제어
     */
    function toggleConfirmButton() {
        const hasSelection = memberListBody.querySelectorAll('.popup-member-checkbox:checked').length > 0;
        if (confirmBtn) {
            confirmBtn.disabled = !hasSelection;
        }
    }

    /**
     * 전체 선택 제어
     */
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', (event) => {
            const isChecked = event.target.checked;
            memberListBody.querySelectorAll('.popup-member-checkbox:not(:disabled)').forEach((checkbox) => {
                checkbox.checked = isChecked;
            });
            toggleConfirmButton();
        });
    }

    if (openBtn) {
        openBtn.addEventListener('click', () => openMemberSearchModal());
    }

    if (searchBtn) {
        searchBtn.addEventListener('click', () => searchMembers(1));
    }

    if (confirmBtn) {
        confirmBtn.addEventListener('click', addSelectedMembers);
    }

    if (searchInput) {
        searchInput.addEventListener('keypress', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
                searchMembers(1);
            }
        });
    }

    renderSelectedMembersEmptyRow();
});



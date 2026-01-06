document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('member-search-modal');
    const openBtn = document.getElementById('openMemberSearchBtn');
    const searchBtn = document.getElementById('popup-search-btn');
    const confirmBtn = document.getElementById('popup-add-btn');
    const selectAllCheckbox = document.getElementById('popup-select-all');
    const searchTypeSelect = document.getElementById('popup_search_type');
    const searchInput = document.getElementById('popup_search_keyword');
    const memberGroupSelect = document.getElementById('member_group_id');
    const memberListBody = document.getElementById('popup-member-list-body');
    const paginationContainer = document.getElementById('popup-pagination');
    const selectedMembersBody = document.getElementById('selectedMembersBody');
    const selectedMembersDisplay = document.getElementById('selected_members_display');
    const memberSelectionStatus = document.getElementById('memberSelectionStatus');

    if (!modal || !openBtn || !memberListBody || !selectedMembersBody) {
        return;
    }

    const config = window.mailSmsFormConfig || {};
    const selectedMemberIds = new Set();
    let isGroupSelectionMode = false;

    /**
     * 초기 선택 상태 구성
     */
    Array.from(selectedMembersBody.querySelectorAll('input[name="member_ids[]"]')).forEach((input) => {
        const value = parseInt(input.value, 10);
        if (value) {
            selectedMemberIds.add(value);
        }
    });

    // 서버에서 렌더링된 회원 행이 있는지 확인
    const existingMemberRows = selectedMembersBody.querySelectorAll('tr[data-member-id]');
    if (existingMemberRows.length > 0 && selectedMemberIds.size === 0) {
        // 서버에서 렌더링된 회원이 있지만 selectedMemberIds에 없으면 추가
        existingMemberRows.forEach((row) => {
            const memberId = parseInt(row.dataset.memberId, 10);
            if (memberId) {
                selectedMemberIds.add(memberId);
            }
        });
    }

    /**
     * 모달 열기
     */
    function openMemberSearchModal() {
        modal.style.display = 'flex';
        
        // 검색 필드 초기화
        if (searchTypeSelect) searchTypeSelect.value = 'all';
        if (searchInput) searchInput.value = '';
        
        clearModalSelection();
        
        // 기본 검색 실행 (검색어 없이 전체 목록)
            searchMembers(1);
    }

    /**
     * 모달 닫기
     */
    function closeMemberSearchModal() {
        modal.style.display = 'none';
        
        // 검색 필드 초기화
        if (searchTypeSelect) searchTypeSelect.value = 'all';
        if (searchInput) searchInput.value = '';
        
        // 검색 결과 초기화
        const selectionMode = modal.dataset.selectionMode || 'multiple';
        const colspan = selectionMode === 'multiple' ? 6 : 5;
        memberListBody.innerHTML = `<tr><td colspan="${colspan}" class="text-center">검색어를 입력하거나 필터를 선택해주세요.</td></tr>`;
        if (paginationContainer) {
            paginationContainer.innerHTML = '';
        }
        
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
        // 실제 회원 행이 있는지 확인 (hidden input 기준)
        const hasMemberRows = selectedMembersBody.querySelectorAll('tr[data-member-id]').length > 0;
        
        if (hasMemberRows || selectedMemberIds.size > 0) {
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
     * 선택된 회원 표시 input 업데이트
     */
    function updateSelectedMembersDisplay() {
        if (!selectedMembersDisplay) {
            return;
        }

        const names = Array.from(selectedMembersBody.querySelectorAll('tr[data-member-id] td:first-child'))
            .map((cell) => cell.textContent.trim())
            .filter((text) => !!text);

        selectedMembersDisplay.value = names.join(', ');
    }

    /**
     * 상태 문구 출력
     */
    function setMemberSelectionStatus(message, tone = 'info') {
        if (!memberSelectionStatus) {
            return;
        }
        memberSelectionStatus.textContent = message;
        memberSelectionStatus.classList.remove('text-danger', 'text-success', 'text-muted');
        const classMap = {
            error: 'text-danger',
            success: 'text-success',
            info: 'text-muted',
        };
        memberSelectionStatus.classList.add(classMap[tone] || 'text-muted');
    }

    /**
     * 검색 버튼 상태 제어
     */
    function toggleSearchButton(disabled) {
        if (!openBtn) {
            return;
        }
        openBtn.disabled = disabled;
        if (disabled) {
            openBtn.classList.add('disabled');
            if (openBtn.dataset.disabledLabel) {
                openBtn.setAttribute('title', openBtn.dataset.disabledLabel);
            }
        } else {
            openBtn.classList.remove('disabled');
            openBtn.removeAttribute('title');
        }
    }

    /**
     * 선택된 회원 목록 비우기
     */
    function clearSelectedMembers() {
        selectedMemberIds.clear();
        selectedMembersBody.innerHTML = '';
        renderSelectedMembersEmptyRow();
        updateSelectedMembersDisplay();
    }

    /**
     * 선택된 회원 행 생성
     */
    function createSelectedMemberRow(member, options = {}) {
        const removable = options.removable !== false;
        const row = document.createElement('tr');
        row.dataset.memberId = member.id ? member.id.toString() : '';

        const removeButton = removable
            ? `<button type="button" class="btn btn-outline-danger btn-sm selected-member-remove" data-member-id="${member.id}">삭제</button>`
            : '<span class="text-muted">자동 선택</span>';

        row.innerHTML = `
            <td>${member.name || '-'}</td>
            <td>${member.email || '-'}</td>
            <td>${member.contact || '-'}</td>
            <td>
                ${removeButton}
                <input type="hidden" name="member_ids[]" value="${member.id}">
            </td>
        `;

        return row;
    }

    /**
     * 선택된 회원에 추가
     */
    function addMembersToSelection(members, options = {}) {
        if (!Array.isArray(members) || members.length === 0) {
            renderSelectedMembersEmptyRow();
            updateSelectedMembersDisplay();
            return;
        }

        members.forEach((member) => {
            const memberId = parseInt(member.id, 10);
            if (!memberId || selectedMemberIds.has(memberId)) {
                return;
            }

            selectedMemberIds.add(memberId);
            selectedMembersBody.appendChild(
                createSelectedMemberRow(
                    {
                        id: memberId,
                        name: member.name || '-',
                        email: member.email || '-',
                        contact: member.contact || '-',
                    },
                    options
                )
            );
        });

        renderSelectedMembersEmptyRow();
        updateSelectedMembersDisplay();
    }

    /**
     * 회원 목록을 새로 구성
     */
    function replaceSelectedMembers(members, options = {}) {
        selectedMembersBody.innerHTML = '';
        selectedMemberIds.clear();
        addMembersToSelection(members, options);
    }

    /**
     * 회원 그룹 조회 URL 생성
     */
    function buildGroupMembersUrl(groupId) {
        if (!config.groupMembersUrlTemplate || !groupId) {
            return null;
        }
        return config.groupMembersUrlTemplate.replace('__GROUP_ID__', groupId);
    }

    /**
     * 회원 그룹 구성원 불러오기
     */
    function fetchGroupMembers(groupId) {
        const url = buildGroupMembersUrl(groupId);

        if (!url) {
            setMemberSelectionStatus('회원 그룹 조회 경로가 설정되지 않았습니다.', 'error');
            return;
        }

        isGroupSelectionMode = true;
        toggleSearchButton(true);
        setMemberSelectionStatus('선택한 회원 그룹의 회원 정보를 불러오는 중입니다...', 'info');

        selectedMembersBody.innerHTML = `
            <tr class="selected-member-loading">
                <td colspan="4" class="text-center text-muted">회원 정보를 불러오는 중입니다...</td>
            </tr>
        `;

        fetch(url, {
            headers: {
                'X-CSRF-TOKEN': config.csrfToken || '',
                Accept: 'application/json',
            },
        })
            .then((response) => {
                if (!response.ok) {
                    throw new Error('회원 그룹 정보를 불러오지 못했습니다.');
                }
                return response.json();
            })
            .then((data) => {
                const members = (data && data.members) || [];
                replaceSelectedMembers(members, { removable: false });
                const meta = data && data.meta ? data.meta : {};
                const groupName = meta.group_name || '선택한 회원 그룹';
                const count = typeof meta.count === 'number' ? meta.count : members.length;

                if (members.length === 0) {
                    setMemberSelectionStatus(`${groupName}에 등록된 회원이 없습니다.`, 'error');
                } else {
                    setMemberSelectionStatus(`${groupName} 회원 ${count}명이 자동으로 선택되었습니다.`, 'success');
                }
            })
            .catch((error) => {
                console.error(error);
                memberGroupSelect.value = '';
                isGroupSelectionMode = false;
                toggleSearchButton(false);
                clearSelectedMembers();
                setMemberSelectionStatus('회원 그룹 정보를 불러오지 못했습니다. 다시 시도해 주세요.', 'error');
            });
    }

    /**
     * 그룹 선택 해제 시 초기화
     */
    function resetToManualSelection() {
        isGroupSelectionMode = false;
        toggleSearchButton(false);
        clearSelectedMembers();
        setMemberSelectionStatus('회원 그룹을 선택하거나 검색 버튼으로 회원을 추가해 주세요.', 'info');
    }

    /**
     * 회원 그룹 변경 처리
     */
    function handleMemberGroupChange() {
        const groupId = memberGroupSelect ? memberGroupSelect.value : '';
        if (groupId) {
            fetchGroupMembers(groupId);
        } else {
            resetToManualSelection();
        }
    }

    /**
     * 회원 목록 조회
     */
    function searchMembers(page = 1) {
        const params = new URLSearchParams();
        const searchType = searchTypeSelect?.value || 'all';
        const keyword = searchInput?.value || '';
        const groupId = memberGroupSelect?.value || '';

        params.append('search_type', searchType);
        params.append('search_keyword', keyword);
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
                '<tr><td colspan="7" class="text-center text-muted">검색 결과가 없습니다.</td></tr>';
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
                        <td>${member.contact || '-'}</td>
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
        if (!paginationContainer) {
            return;
        }

        if (!pagination) {
            paginationContainer.innerHTML = '';
            return;
        }

        const lastPage = Math.max(1, pagination.last_page ?? 1);
        const current = Math.min(lastPage, pagination.current_page ?? 1);
        const startPage = Math.max(1, current - 2);
        const endPage = Math.min(lastPage, current + 2);

        let html = '<nav aria-label="페이지 네비게이션"><ul class="pagination">';

        const pageItem = (page, label, disabled = false) => {
            if (disabled || !page || page < 1) {
                return `<li class="page-item disabled"><span class="page-link">${label}</span></li>`;
            }
            return `<li class="page-item"><a href="#" class="page-link" data-page="${page}">${label}</a></li>`;
        };

        html += pageItem(1, '<i class="fas fa-angle-double-left"></i>', current === 1);
        html += pageItem(current > 1 ? current - 1 : 0, '<i class="fas fa-chevron-left"></i>', current === 1);

        // 페이지 번호 표시 (최소 1페이지는 항상 표시)
        if (lastPage === 1) {
            html += `<li class="page-item active"><span class="page-link">1</span></li>`;
        } else {
            for (let i = startPage; i <= endPage; i++) {
                if (i === current) {
                    html += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
            } else {
                    html += pageItem(i, i);
            }
        }
        }

        html += pageItem(current < lastPage ? current + 1 : 0, '<i class="fas fa-chevron-right"></i>', current === lastPage);
        html += pageItem(lastPage, '<i class="fas fa-angle-double-right"></i>', current === lastPage);

        html += '</ul></nav>';
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

        const members = Array.from(checkboxes).map((checkbox) => ({
            id: parseInt(checkbox.value, 10),
            name: checkbox.dataset.name || '-',
            email: checkbox.dataset.email || '-',
            contact: checkbox.dataset.contact || '-',
        }));

        addMembersToSelection(members, { removable: true });
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
        updateSelectedMembersDisplay();
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

    // 팝업 초기화 버튼 클릭
    const popupResetBtn = document.getElementById('popup-reset-btn');
    if (popupResetBtn) {
        popupResetBtn.addEventListener('click', function() {
            // 검색 필드 초기화
            if (searchTypeSelect) searchTypeSelect.value = 'all';
            if (searchInput) searchInput.value = '';
            
            // 전체 목록 다시 로드
            searchMembers(1);
        });
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

    if (memberGroupSelect) {
        memberGroupSelect.addEventListener('change', handleMemberGroupChange);
        if (memberGroupSelect.value) {
            fetchGroupMembers(memberGroupSelect.value);
        } else {
            // 편집 모드에서는 기존 회원 정보를 유지
            const hasExistingMembers = selectedMemberIds.size > 0;
            if (!hasExistingMembers) {
            resetToManualSelection();
            } else {
                // 기존 회원이 있으면 상태만 업데이트
                toggleSearchButton(false);
                setMemberSelectionStatus('회원 그룹을 선택하거나 검색 버튼으로 회원을 추가해 주세요.', 'info');
            }
        }
    } else {
        // 기존 회원이 있으면 상태만 업데이트
        const hasExistingMembers = selectedMemberIds.size > 0;
        if (hasExistingMembers) {
            setMemberSelectionStatus('회원 그룹을 선택하거나 검색 버튼으로 회원을 추가해 주세요.', 'info');
    } else {
        setMemberSelectionStatus('회원 그룹을 선택하거나 검색 버튼으로 회원을 추가해 주세요.', 'info');
        }
    }

    toggleSearchButton(isGroupSelectionMode);
    renderSelectedMembersEmptyRow();
    updateSelectedMembersDisplay();
});



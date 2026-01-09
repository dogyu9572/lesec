// 회원 그룹 관리 페이지 JavaScript

let currentPage = 1;
let selectedMemberIds = [];

/**
 * 팝업에서 선택한 회원을 받아서 그룹에 추가
 * individual-applications/create와 동일한 방식으로 단일 회원 선택 지원
 * 여러 명 선택 시 배열로 받아서 처리
 */
window.applySelectedMember = function(selectedMember) {
    if (!selectedMember) {
        return;
    }

    // 배열로 전달된 경우 (여러 명 선택)
    const selectedMembers = Array.isArray(selectedMember) ? selectedMember : [selectedMember];
    
    if (selectedMembers.length === 0) {
        return;
    }

    const currentGroupId = typeof groupId !== 'undefined' ? groupId : null;
    
    // create 페이지인 경우 (groupId가 없음) - 테이블에 직접 추가
    if (!currentGroupId) {
        const memberListBody = document.getElementById('member-list-body');
        const memberGroupForm = document.getElementById('memberGroupForm');
        if (!memberListBody || !memberGroupForm) {
            alert('회원 목록을 찾을 수 없습니다.');
            return;
        }

        // 먼저 회원 검증 (이미 그룹에 속한 회원 확인)
        const memberIds = selectedMembers.map(member => member.id).filter(id => id);
        if (memberIds.length === 0) {
            return;
        }

        const validateFormData = new FormData();
        memberIds.forEach(id => validateFormData.append('member_ids[]', id));

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        // 회원 검증 API 호출
        fetch('/backoffice/member-groups/validate-members', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: validateFormData
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                // 이미 그룹에 속한 회원이 있으면 에러 메시지 표시하고 추가하지 않음
                alert(data.message);
                return;
            }

            // 검증 통과 시 회원 추가
            // 기존 "등록된 회원이 없습니다" 메시지 제거
            const emptyRow = memberListBody.querySelector('tr[style*="border: none"]');
            if (emptyRow) {
                emptyRow.remove();
            }

            // 선택한 회원들을 테이블에 추가
            selectedMembers.forEach((member, index) => {
            const existingRow = memberListBody.querySelector(`tr[data-member-id="${member.id}"]`);
            if (existingRow) {
                return; // 이미 추가된 회원은 건너뛰기
            }

            const row = document.createElement('tr');
            row.dataset.memberId = member.id;
            const rowNumber = memberListBody.querySelectorAll('tr[data-member-id]').length + 1;
            
            row.innerHTML = `
                <td>${rowNumber}</td>
                <td>${member.name || '-'}</td>
                <td>${member.contact || '-'}</td>
                <td>${member.parent_contact || '-'}</td>
                <td>${member.email || '-'}</td>
                <td>-</td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm remove-member-btn" data-member-id="${member.id}">
                        <i class="fas fa-trash"></i> 삭제
                    </button>
                </td>
            `;

            // hidden input 추가 (폼 제출 시 사용) - 폼에 직접 추가
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'member_ids[]';
            hiddenInput.value = member.id;
            hiddenInput.dataset.memberId = member.id;
            hiddenInput.id = `member_input_${member.id}`;
            memberGroupForm.appendChild(hiddenInput);

                memberListBody.appendChild(row);
            });

            // 삭제 버튼 이벤트 리스너 추가
            memberListBody.querySelectorAll('.remove-member-btn').forEach(btn => {
                if (!btn.hasAttribute('data-listener-added')) {
                    btn.setAttribute('data-listener-added', 'true');
                    btn.addEventListener('click', function() {
                        const memberId = parseInt(this.getAttribute('data-member-id'));
                        const row = memberListBody.querySelector(`tr[data-member-id="${memberId}"]`);
                        
                        // 테이블에서 행 제거
                        if (row) {
                            row.remove();
                        }
                        
                        // 폼에서 hidden input 제거
                        const hiddenInput = document.getElementById(`member_input_${memberId}`);
                        if (hiddenInput) {
                            hiddenInput.remove();
                        }
                        
                        // 회원이 없으면 빈 메시지 표시
                        if (memberListBody.querySelectorAll('tr[data-member-id]').length === 0) {
                            memberListBody.innerHTML = '<tr style="border: none;"><td colspan="7" class="text-center" style="padding: 40px 20px; border: none !important; border-bottom: none !important;">등록된 회원이 없습니다.</td></tr>';
                        } else {
                            // 번호 재정렬
                            memberListBody.querySelectorAll('tr[data-member-id]').forEach((row, index) => {
                                row.querySelector('td:first-child').textContent = index + 1;
                            });
                        }
                    });
                }
            });

            // 번호 재정렬
            memberListBody.querySelectorAll('tr[data-member-id]').forEach((row, index) => {
                row.querySelector('td:first-child').textContent = index + 1;
            });
        })
        .catch(error => {
            console.error('회원 검증 중 오류:', error);
            alert('회원 검증 중 오류가 발생했습니다.');
        });

        return;
    }

    // edit 페이지인 경우도 create처럼 테이블에만 추가 (저장 버튼 클릭 시 저장)
    const memberListBody = document.getElementById('member-list-body');
    const memberGroupForm = document.getElementById('memberGroupForm');
    if (!memberListBody || !memberGroupForm) {
        alert('회원 목록을 찾을 수 없습니다.');
        return;
    }

    // 먼저 회원 검증 (이미 그룹에 속한 회원 확인)
    const memberIds = selectedMembers.map(member => member.id).filter(id => id);
    if (memberIds.length === 0) {
        return;
    }

    const validateFormData = new FormData();
    memberIds.forEach(id => validateFormData.append('member_ids[]', id));

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    // 회원 검증 API 호출
    fetch('/backoffice/member-groups/validate-members', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        },
        body: validateFormData
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            // 이미 그룹에 속한 회원이 있으면 에러 메시지 표시하고 추가하지 않음
            alert(data.message);
            return;
        }

        // 검증 통과 시 회원 추가
        // 기존 "등록된 회원이 없습니다" 메시지 제거
        const emptyRow = memberListBody.querySelector('tr[style*="border: none"]');
        if (emptyRow) {
            emptyRow.remove();
        }

        // 선택한 회원들을 테이블에 추가
        selectedMembers.forEach((member, index) => {
            const existingRow = memberListBody.querySelector(`tr[data-member-id="${member.id}"]`);
            if (existingRow) {
                return; // 이미 추가된 회원은 건너뛰기
            }

            const row = document.createElement('tr');
            row.dataset.memberId = member.id;
            const rowNumber = memberListBody.querySelectorAll('tr[data-member-id]').length + 1;
            
            row.innerHTML = `
                <td>${rowNumber}</td>
                <td>${member.name || '-'}</td>
                <td>${member.contact || '-'}</td>
                <td>${member.parent_contact || '-'}</td>
                <td>${member.email || '-'}</td>
                <td>-</td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm remove-member-btn" data-member-id="${member.id}">
                        <i class="fas fa-trash"></i> 삭제
                    </button>
                </td>
            `;

            // hidden input 추가 (폼 제출 시 사용) - 폼에 직접 추가
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'member_ids[]';
            hiddenInput.value = member.id;
            hiddenInput.dataset.memberId = member.id;
            hiddenInput.id = `member_input_${member.id}`;
            hiddenInput.classList.add('new-member-input'); // 새로 추가된 회원 표시
            memberGroupForm.appendChild(hiddenInput);

            memberListBody.appendChild(row);
        });

        // 삭제 버튼 이벤트 리스너 추가
        memberListBody.querySelectorAll('.remove-member-btn').forEach(btn => {
            if (!btn.hasAttribute('data-listener-added')) {
                btn.setAttribute('data-listener-added', 'true');
                btn.addEventListener('click', function() {
                    const memberId = parseInt(this.getAttribute('data-member-id'));
                    const row = memberListBody.querySelector(`tr[data-member-id="${memberId}"]`);
                    
                    // 테이블에서 행 제거
                    if (row) {
                        row.remove();
                    }
                    
                    // 폼에서 hidden input 제거
                    const hiddenInput = document.getElementById(`member_input_${memberId}`);
                    if (hiddenInput) {
                        hiddenInput.remove();
                    }
                    
                    // 회원이 없으면 빈 메시지 표시
                    if (memberListBody.querySelectorAll('tr[data-member-id]').length === 0) {
                        memberListBody.innerHTML = '<tr style="border: none;"><td colspan="7" class="text-center" style="padding: 40px 20px; border: none !important; border-bottom: none !important;">등록된 회원이 없습니다.</td></tr>';
                    } else {
                        // 번호 재정렬
                        memberListBody.querySelectorAll('tr[data-member-id]').forEach((row, index) => {
                            row.querySelector('td:first-child').textContent = index + 1;
                        });
                    }
                });
            }
        });

        // 번호 재정렬
        memberListBody.querySelectorAll('tr[data-member-id]').forEach((row, index) => {
            row.querySelector('td:first-child').textContent = index + 1;
        });
    })
    .catch(error => {
        console.error('회원 검증 중 오류:', error);
        alert('회원 검증 중 오류가 발생했습니다.');
    });
};

document.addEventListener('DOMContentLoaded', function() {
    if (typeof groupId === 'undefined') {
        window.groupId = null;
    }

    // 회원 추가 버튼 클릭
    const addMemberBtn = document.getElementById('add-member-btn');
    if (addMemberBtn) {
        addMemberBtn.addEventListener('click', function() {
            openMemberSearchModal();
        });
    }

    // edit 페이지에서 기존 회원 삭제 버튼 이벤트 리스너 추가
    const memberListBody = document.getElementById('member-list-body');
    if (memberListBody) {
        memberListBody.querySelectorAll('.remove-member-btn').forEach(btn => {
            if (!btn.hasAttribute('data-listener-added')) {
                btn.setAttribute('data-listener-added', 'true');
                btn.addEventListener('click', function() {
                    const memberId = parseInt(this.getAttribute('data-member-id'));
                    const row = memberListBody.querySelector(`tr[data-member-id="${memberId}"]`);
                    
                    // 테이블에서 행 제거
                    if (row) {
                        row.remove();
                    }
                    
                    // 폼에서 hidden input 제거
                    const hiddenInput = document.getElementById(`member_input_${memberId}`);
                    if (hiddenInput) {
                        hiddenInput.remove();
                    }
                    
                    // 회원이 없으면 빈 메시지 표시
                    if (memberListBody.querySelectorAll('tr[data-member-id]').length === 0) {
                        memberListBody.innerHTML = '<tr style="border: none;"><td colspan="7" class="text-center" style="padding: 40px 20px; border: none !important; border-bottom: none !important;">등록된 회원이 없습니다.</td></tr>';
                    } else {
                        // 번호 재정렬
                        memberListBody.querySelectorAll('tr[data-member-id]').forEach((row, index) => {
                            row.querySelector('td:first-child').textContent = index + 1;
                        });
                    }
                });
            }
        });
    }

    // 팝업 검색 버튼 클릭
    const popupSearchBtn = document.getElementById('popup-search-btn');
    if (popupSearchBtn) {
        popupSearchBtn.addEventListener('click', function() {
            searchMembers(1);
        });
    }

    // 팝업 초기화 버튼 클릭
    const popupResetBtn = document.getElementById('popup-reset-btn');
    if (popupResetBtn) {
        popupResetBtn.addEventListener('click', function() {
            // 검색 필드 초기화
            const searchTypeElement = document.getElementById('popup_search_type');
            const searchKeywordElement = document.getElementById('popup_search_keyword');
            if (searchTypeElement) searchTypeElement.value = 'all';
            if (searchKeywordElement) searchKeywordElement.value = '';
            
            // 전체 목록 다시 로드
            searchMembers(1);
        });
    }

    // 팝업 선택 완료 버튼 클릭
    const popupAddBtn = document.getElementById('popup-add-btn');
    if (popupAddBtn) {
        popupAddBtn.addEventListener('click', function() {
            addSelectedMembers();
        });
    }

    // 팝업 전체 선택 체크박스
    const popupSelectAll = document.getElementById('popup-select-all');
    if (popupSelectAll) {
        popupSelectAll.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.popup-member-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
                if (this.checked) {
                    if (!selectedMemberIds.includes(parseInt(checkbox.value))) {
                        selectedMemberIds.push(parseInt(checkbox.value));
                    }
                } else {
                    selectedMemberIds = selectedMemberIds.filter(id => id !== parseInt(checkbox.value));
                }
            });
            updatePopupAddButton();
        });
    }

    // 회원 삭제 버튼 (edit 페이지)
    const removeMemberBtns = document.querySelectorAll('.remove-member-btn');
    removeMemberBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const memberId = parseInt(this.getAttribute('data-member-id'));
            removeMemberFromGroup(memberId);
        });
    });

    // 검색어 입력 시 엔터키 처리
    const popupSearchKeyword = document.getElementById('popup_search_keyword');
    if (popupSearchKeyword) {
        popupSearchKeyword.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                searchMembers(1);
            }
        });
    }

    // 폼 제출 전 데이터 확인 (회원 그룹 생성 페이지)
    const memberGroupForm = document.getElementById('memberGroupForm');
    if (memberGroupForm) {
        memberGroupForm.addEventListener('submit', function(e) {
            const memberInputs = this.querySelectorAll('input[name="member_ids[]"]');
            console.log('=== 폼 제출 전 회원 데이터 확인 ===');
            console.log('회원 ID 개수:', memberInputs.length);
            console.log('회원 ID 목록:', Array.from(memberInputs).map(input => input.value));
            
            // FormData로 실제 전송될 데이터 확인
            const formData = new FormData(this);
            const memberIds = formData.getAll('member_ids[]');
            console.log('전송될 member_ids[]:', memberIds);
            console.log('전체 FormData:');
            for (let [key, value] of formData.entries()) {
                console.log(`  ${key}:`, value);
            }
            console.log('================================');
        });
    }
});

/**
 * 회원 검색 팝업 열기
 */
function openMemberSearchModal() {
    const currentGroupId = typeof groupId !== 'undefined' ? groupId : null;
    let url = '/backoffice/popup-windows/member-search?selection_mode=multiple';
    if (currentGroupId) {
        url += `&group_id=${currentGroupId}`;
    }
    const width = window.innerWidth <= 768 ? '100%' : '1000';
    const height = window.innerHeight <= 768 ? '100%' : '700';
    window.open(url, 'memberSearch', `width=${width},height=${height},left=100,top=100,scrollbars=yes,resizable=yes`);
}


/**
 * 회원 검색
 */
function searchMembers(page = 1) {
    currentPage = page;
    const searchTypeElement = document.getElementById('popup_search_type');
    const searchKeywordElement = document.getElementById('popup_search_keyword');
    
    const searchType = searchTypeElement?.value || 'all';
    const searchKeyword = searchKeywordElement?.value?.trim() || '';

    const url = new URL('/backoffice/member-groups/search-members', window.location.origin);
    if (searchKeyword) {
        url.searchParams.append('search_type', searchType);
        url.searchParams.append('search_keyword', searchKeyword);
    }
    url.searchParams.append('exclude_grouped', 'true');
    url.searchParams.append('page', page);
    url.searchParams.append('per_page', '10');

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    fetch(url, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        }
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => {
                throw new Error(err.message || '회원 검색 중 오류가 발생했습니다.');
            });
        }
        return response.json();
    })
    .then(data => {
        if (!data.members) {
            data.members = [];
        }
        if (!data.pagination) {
            data.pagination = {
                current_page: 1,
                last_page: 1,
                per_page: 10,
                total: 0
            };
        }
        renderMemberList(data.members);
        renderPagination(data.pagination);
        selectedMemberIds = [];
        updatePopupAddButton();
    })
    .catch(error => {
        console.error('Error:', error);
        alert(error.message || '회원 검색 중 오류가 발생했습니다.');
        // 에러 발생 시 빈 목록 표시
        renderMemberList([]);
        renderPagination({
            current_page: 1,
            last_page: 1,
            per_page: 10,
            total: 0
        });
    });
}

/**
 * 회원 목록 렌더링
 */
function renderMemberList(members) {
    const tbody = document.getElementById('popup-member-list-body');
    if (!tbody) {
        return;
    }

    if (!members || members.length === 0) {
        tbody.innerHTML = `<tr><td colspan="7" class="text-center">검색 결과가 없습니다.</td></tr>`;
        return;
    }

    let html = '';
    members.forEach((member, index) => {
        const no = (currentPage - 1) * 10 + index + 1;
        html += `
            <tr>
                <td>
                    <input type="checkbox" class="popup-member-checkbox" value="${member.id}" 
                        onchange="handleMemberCheckboxChange(this)">
                </td>
                <td>${no}</td>
                <td>${member.login_id || '-'}</td>
                <td>${member.name || '-'}</td>
                <td>${member.school_name || '-'}</td>
                <td>${member.email || '-'}</td>
                <td>${member.contact || '-'}</td>
            </tr>
        `;
    });

    tbody.innerHTML = html;

    // 체크박스 이벤트 리스너 추가
    document.querySelectorAll('.popup-member-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            handleMemberCheckboxChange(this);
        });
    });
}

/**
 * 체크박스 변경 처리
 */
function handleMemberCheckboxChange(checkbox) {
    const memberId = parseInt(checkbox.value);
    
    if (checkbox.checked) {
        if (!selectedMemberIds.includes(memberId)) {
            selectedMemberIds.push(memberId);
        }
    } else {
        selectedMemberIds = selectedMemberIds.filter(id => id !== memberId);
    }
    
    updatePopupAddButton();
    updatePopupSelectAll();
}

/**
 * 팝업 전체 선택 체크박스 업데이트
 */
function updatePopupSelectAll() {
    const popupSelectAll = document.getElementById('popup-select-all');
    const checkboxes = document.querySelectorAll('.popup-member-checkbox');
    const checkedCount = Array.from(checkboxes).filter(cb => cb.checked).length;
    
    if (popupSelectAll && checkboxes.length > 0) {
        popupSelectAll.checked = checkedCount === checkboxes.length;
    }
}

/**
 * 팝업 추가 버튼 상태 업데이트
 */
function updatePopupAddButton() {
    const popupAddBtn = document.getElementById('popup-add-btn');
    if (popupAddBtn) {
        popupAddBtn.disabled = selectedMemberIds.length === 0;
    }
}

/**
 * 페이지네이션 렌더링
 */
function renderPagination(pagination) {
    const container = document.getElementById('popup-pagination');
    if (!container) {
        return;
    }

    if (!pagination) {
        container.innerHTML = '';
        return;
    }

    const lastPage = Math.max(1, pagination.last_page ?? 1);
    const current = Math.min(lastPage, pagination.current_page ?? currentPage ?? 1);
    const startPage = Math.max(1, current - 2);
    const endPage = Math.min(lastPage, current + 2);
    
    // 항상 페이지네이션 표시 (최소 1페이지)

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
        // 1페이지만 있을 때는 현재 페이지만 표시
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
    container.innerHTML = html;

    container.querySelectorAll('a[data-page]').forEach((link) => {
        link.addEventListener('click', function (event) {
            event.preventDefault();
            const targetPage = Number(this.dataset.page);
            if (!targetPage || targetPage === currentPage) {
                return;
            }
            searchMembers(targetPage);
        });
    });
}

/**
 * 선택한 회원을 그룹에 추가
 */
function addSelectedMembers() {
    if (selectedMemberIds.length === 0) {
        alert('추가할 회원을 선택해주세요.');
        return;
    }

    const currentGroupId = typeof groupId !== 'undefined' ? groupId : null;
    if (!currentGroupId) {
        alert('그룹이 생성된 후 회원을 추가할 수 있습니다.');
        return;
    }

    const formData = new FormData();
    selectedMemberIds.forEach(id => formData.append('member_ids[]', id));

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const url = `/backoffice/member-groups/${currentGroupId}/add-members`;

    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            closeMemberSearchModal();
            location.reload();
        } else {
            alert('회원 추가 중 오류가 발생했습니다: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('회원 추가 중 오류가 발생했습니다.');
    });
}

/**
 * 그룹에서 회원 제거
 */
function removeMemberFromGroup(memberId) {
    const currentGroupId = typeof groupId !== 'undefined' ? groupId : null;
    if (!currentGroupId) {
        alert('그룹 정보를 찾을 수 없습니다.');
        return;
    }

    if (!confirm('이 회원을 그룹에서 제거하시겠습니까?')) {
        return;
    }

    const formData = new FormData();
    formData.append('member_id', memberId);

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const url = `/backoffice/member-groups/${currentGroupId}/remove-member`;

    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('회원 제거 중 오류가 발생했습니다: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('회원 제거 중 오류가 발생했습니다.');
    });
}


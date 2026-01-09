/**
 * 백오피스 회원 검색 팝업 제어 스크립트
 */

let currentMemberPage = 1;
let selectedMemberIds = [];
let memberSearchUrl = '/backoffice/individual-applications/search-members';
let selectionMode = 'single'; // 기본값: 단일 선택 (individual-applications용)

document.addEventListener('DOMContentLoaded', function () {
    // URL 파라미터에서 선택 모드 확인
    const urlParams = new URLSearchParams(window.location.search);
    const urlSelectionMode = urlParams.get('selection_mode');
    // selection_mode=multiple이 명시된 경우만 여러 명 선택 가능
    selectionMode = urlSelectionMode === 'multiple' ? 'multiple' : 'single';

    const searchForm = document.getElementById('member-search-form');
    if (searchForm && searchForm.dataset.memberSearchUrl) {
        memberSearchUrl = searchForm.dataset.memberSearchUrl;
    }

    const popupSearchBtn = document.getElementById('popup-search-btn');
    const popupMemberConfirm = document.getElementById('popup-add-btn');
    const popupSelectAll = document.getElementById('popup-select-all');
    const popupResetBtn = document.getElementById('popup-reset-btn');
    const popupSearchKeyword = document.getElementById('popup_search_keyword');

    // 페이지 로드 시 기본 검색 실행
    searchMembers(1);

    // 회원 검색
    if (popupSearchBtn) {
        popupSearchBtn.addEventListener('click', function () {
            searchMembers(1);
        });
    }

    // 팝업 초기화 버튼 클릭
    if (popupResetBtn) {
        popupResetBtn.addEventListener('click', function() {
            // 검색 필드 초기화
            const memberTypeElement = document.getElementById('popup_member_type');
            const searchTypeElement = document.getElementById('popup_search_type');
            const searchKeywordElement = document.getElementById('popup_search_keyword');
            if (memberTypeElement) memberTypeElement.value = '';
            if (searchTypeElement) searchTypeElement.value = 'all';
            if (searchKeywordElement) searchKeywordElement.value = '';
            
            // 전체 목록 다시 로드
            searchMembers(1);
        });
    }

    // 회원 검색 엔터키
    if (popupSearchKeyword) {
        popupSearchKeyword.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                searchMembers(1);
            }
        });
    }

    // 회원 선택 확인
    if (popupMemberConfirm) {
        popupMemberConfirm.addEventListener('click', function () {
            if (selectedMemberIds.length > 0) {
                applySelectedMember();
            }
        });
    }

    // 전체 선택 체크박스
    if (popupSelectAll) {
        popupSelectAll.addEventListener('change', function () {
            const checkboxes = document.querySelectorAll('.popup-member-checkbox');
            checkboxes.forEach((checkbox) => {
                checkbox.checked = this.checked;
                const memberId = parseInt(checkbox.value);
                if (this.checked) {
                    if (!selectedMemberIds.includes(memberId)) {
                        selectedMemberIds.push(memberId);
                    }
                } else {
                    selectedMemberIds = selectedMemberIds.filter(id => id !== memberId);
                }
            });
            updateMemberConfirmButton();
        });
    }
});

// 회원 검색
function searchMembers(page) {
    currentMemberPage = page;
    const form = document.getElementById('member-search-form');
    if (!form) return;

    const formData = new FormData(form);
    const params = new URLSearchParams(formData);
    params.append('page', page);

    const tbody = document.getElementById('popup-member-list-body');
    if (tbody) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center">회원 정보를 불러오는 중입니다...</td></tr>';
    }

    fetch(`${memberSearchUrl}?${params.toString()}`)
        .then(response => response.json())
        .then(data => {
            renderMemberList(data.members, data.pagination);
            renderMemberPagination(data.pagination);
        })
        .catch(error => {
            console.error('회원 검색 오류:', error);
            const tbody = document.getElementById('popup-member-list-body');
            if (tbody) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center text-danger">회원 검색 중 오류가 발생했습니다.</td></tr>';
            }
        });
}

// 회원 목록 렌더링
function renderMemberList(members, pagination) {
    const tbody = document.getElementById('popup-member-list-body');
    if (!tbody) return;

    const total = pagination ? pagination.total : members.length;
    const perPage = pagination ? pagination.per_page : members.length;
    const current = pagination ? pagination.current_page : 1;
    const baseNumber = total - ((current - 1) * perPage);

    if (!members || members.length === 0) {
        tbody.innerHTML = `<tr><td colspan="7" class="text-center">검색 결과가 없습니다.</td></tr>`;
        return;
    }

    tbody.innerHTML = members.map((member, index) => {
        const memberData = encodeURIComponent(JSON.stringify(member));
        const rowNumber = Math.max(1, baseNumber - index);
        const isChecked = selectedMemberIds.includes(member.id);

        return `
            <tr>
                <td>
                    <input type="checkbox" 
                        class="popup-member-checkbox" 
                        value="${member.id}" 
                        data-member="${memberData}"
                        ${isChecked ? 'checked' : ''}>
                </td>
                <td>${rowNumber}</td>
                <td>${member.login_id || '-'}</td>
                <td>${member.name || '-'}</td>
                <td>${member.school_name || '-'}</td>
                <td>${member.email || '-'}</td>
                <td>${member.contact || '-'}</td>
            </tr>
        `;
    }).join('');

    tbody.querySelectorAll('.popup-member-checkbox').forEach((checkbox) => {
        checkbox.addEventListener('change', function () {
            const memberId = parseInt(this.value);
            if (this.checked) {
                if (selectionMode === 'single') {
                    // 단일 선택 모드: 1명만 선택 가능하도록 기존 선택 해제
                    selectedMemberIds = [memberId];
                    tbody.querySelectorAll('.popup-member-checkbox').forEach(cb => {
                        if (parseInt(cb.value) !== memberId) {
                            cb.checked = false;
                        }
                    });
                    const popupSelectAll = document.getElementById('popup-select-all');
                    if (popupSelectAll) {
                        popupSelectAll.checked = false;
                    }
                } else {
                    // 다중 선택 모드: 여러 명 선택 가능
                    if (!selectedMemberIds.includes(memberId)) {
                        selectedMemberIds.push(memberId);
                    }
                }
            } else {
                selectedMemberIds = selectedMemberIds.filter(id => id !== memberId);
            }
            updateMemberConfirmButton();
        });
    });
}

// 회원 페이지네이션 렌더링
function renderMemberPagination(pagination) {
    const container = document.getElementById('popup-pagination');
    if (!container) return;

    const lastPage = Math.max(1, pagination?.last_page ?? 1);
    const current = Math.min(lastPage, pagination?.current_page ?? currentMemberPage);
    const startPage = Math.max(1, current - 2);
    const endPage = Math.min(lastPage, current + 2);

    let html = '<nav aria-label="페이지 네비게이션"><ul class="pagination">';

    const pageItem = (page, label, disabled = false) => {
        if (disabled) {
            return `<li class="page-item disabled"><span class="page-link">${label}</span></li>`;
        }
        return `<li class="page-item"><a href="#" class="page-link" data-member-page="${page}">${label}</a></li>`;
    };

    html += pageItem(1, '<i class="fas fa-angle-double-left"></i>', current === 1);
    html += pageItem(current - 1, '<i class="fas fa-chevron-left"></i>', current === 1);

    for (let i = startPage; i <= endPage; i++) {
        if (i === current) {
            html += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
        } else {
            html += pageItem(i, i);
        }
    }

    html += pageItem(current + 1, '<i class="fas fa-chevron-right"></i>', current === lastPage);
    html += pageItem(lastPage, '<i class="fas fa-angle-double-right"></i>', current === lastPage);

    html += '</ul></nav>';
    container.innerHTML = html;

    container.querySelectorAll('a[data-member-page]').forEach((link) => {
        link.addEventListener('click', function (event) {
            event.preventDefault();
            const targetPage = Number(this.dataset.memberPage);
            if (!targetPage || targetPage === currentMemberPage) {
                return;
            }
            selectedMemberIds = [];
            updateMemberConfirmButton();
            searchMembers(targetPage);
        });
    });
}

// 회원 확인 버튼 상태 업데이트
function updateMemberConfirmButton() {
    const confirmBtn = document.getElementById('popup-add-btn');
    if (confirmBtn) {
        // selection_mode 파라미터가 없으면 여러 명 선택 가능 (member-groups용)
        // selection_mode=single이면 1명만 선택 가능
        if (selectionMode === 'single') {
            confirmBtn.disabled = selectedMemberIds.length !== 1;
        } else {
            // multiple 모드이거나 파라미터가 없으면 여러 명 선택 가능
            confirmBtn.disabled = selectedMemberIds.length === 0;
        }
    }
}

// 선택한 회원 적용 (부모창으로 전달)
function applySelectedMember() {
    if (selectedMemberIds.length === 0) return;

    // 체크된 체크박스에서 회원 정보 가져오기
    const selectedMembers = [];
    selectedMemberIds.forEach(memberId => {
        const checkbox = document.querySelector(`.popup-member-checkbox[value="${memberId}"]`);
        if (checkbox && checkbox.dataset.member) {
            const memberData = JSON.parse(decodeURIComponent(checkbox.dataset.member));
            selectedMembers.push(memberData);
        }
    });

    if (selectedMembers.length === 0) return;

    // 부모창으로 데이터 전달
    if (!window.opener || window.opener.closed) {
        alert('부모창과의 연결이 끊어졌습니다.');
        return;
    }

    // 부모창의 함수가 로드될 때까지 기다리는 함수
    function waitForParentFunction(functionName, maxAttempts = 20, delay = 100) {
        return new Promise((resolve, reject) => {
            let attempts = 0;
            const checkFunction = () => {
                attempts++;
                
                if (window.opener && !window.opener.closed && typeof window.opener[functionName] === 'function') {
                    resolve();
                } else if (attempts >= maxAttempts) {
                    reject(new Error(`${functionName} 함수를 찾을 수 없습니다.`));
                } else {
                    setTimeout(checkFunction, delay);
                }
            };
            checkFunction();
        });
    }

    // 함수 호출 - individual-applications/create와 동일한 방식
    const callParentFunction = async () => {
        try {
            // applySelectedMember 함수 확인 (단수 함수명 사용)
            await waitForParentFunction('applySelectedMember');
            
            // 여러 명 선택 시 배열로 전달, 단일 선택 시 객체로 전달
            if (selectedMembers.length === 1) {
                window.opener.applySelectedMember(selectedMembers[0]);
            } else {
                // 여러 명 선택 시 배열로 전달 (부모창에서 배열 처리)
                window.opener.applySelectedMember(selectedMembers);
            }
            window.close();
        } catch (error) {
            console.error('부모창 통신 오류:', error);
            alert('부모창에서 회원 선택 함수를 찾을 수 없습니다. 페이지를 새로고침 후 다시 시도해주세요.');
        }
    };

    callParentFunction();
}


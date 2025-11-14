/**
 * 신청 내역(개인/단체) 검색 모달 제어 스크립트
 */

let currentMemberPage = 1;
let currentProgramPage = 1;
let selectedMember = null;
let selectedProgram = null;
let memberSearchUrl = '/backoffice/individual-applications/search-members';
let programSearchUrl = '/backoffice/individual-applications/search-programs';

document.addEventListener('DOMContentLoaded', function () {
    const searchConfigElement = document.getElementById('application-search-config');
    if (searchConfigElement) {
        memberSearchUrl = searchConfigElement.dataset.memberSearchUrl || memberSearchUrl;
        programSearchUrl = searchConfigElement.dataset.programSearchUrl || programSearchUrl;
    }

    const programSearchModal = document.getElementById('program-search-modal');
    if (programSearchModal && programSearchModal.dataset.programSearchUrl) {
        programSearchUrl = programSearchModal.dataset.programSearchUrl;
    }
    const programDirectInput = document.getElementById('popup_direct_program_name');
    const programDirectConfirmButton = document.getElementById('popup-confirm-btn');
    if (programDirectConfirmButton) {
        programDirectConfirmButton.disabled = true;
    }

    // 회원 검색 모달
    const memberSearchBtn = document.getElementById('member-search-btn');
    const memberSearchModal = document.getElementById('member-search-modal');
    const memberSearchForm = document.getElementById('member-search-form');
    const popupSearchBtn = document.getElementById('popup-search-btn');
    const popupMemberConfirm = document.getElementById('popup-member-confirm') || document.getElementById('popup-add-btn');
    const memberSearchCancelButtons = document.querySelectorAll('.member-search-cancel');
    const memberModalClose = memberSearchModal ? memberSearchModal.querySelector('.category-modal-close') : null;

    // 프로그램 검색 모달
    const programSearchBtn = document.getElementById('program-search-btn');
    const programSearchForm = document.getElementById('program-search-form');
    const popupProgramSearchBtn = document.getElementById('popup-program-search-btn');
    const popupProgramConfirm = document.getElementById('popup-program-confirm');
    const programSearchCancel = document.querySelectorAll('.program-search-cancel');
    const programModalClose = programSearchModal ? programSearchModal.querySelector('.category-modal-close') : null;

    // 추첨결과 연동
    const receptionTypeSelect = document.getElementById('reception_type');
    const drawResultSelect = document.getElementById('draw_result_select');
    const drawResultHidden = document.getElementById('draw_result');

    function handleDrawResultLock() {
        if (!receptionTypeSelect || !drawResultSelect || !drawResultHidden) {
            return;
        }

        if (receptionTypeSelect.value === 'lottery') {
            drawResultSelect.disabled = false;
        } else {
            drawResultSelect.value = '';
            drawResultSelect.disabled = true;
            drawResultHidden.value = 'pending';
        }
    }

    if (drawResultSelect && drawResultHidden) {
        drawResultSelect.addEventListener('change', function () {
            drawResultHidden.value = drawResultSelect.value;
        });

        drawResultHidden.value = drawResultSelect.value;
    }

    receptionTypeSelect?.addEventListener('change', handleDrawResultLock);
    handleDrawResultLock();

    // 회원 검색 모달 열기
    if (memberSearchBtn) {
        memberSearchBtn.addEventListener('click', function () {
            openMemberSearchModal();
        });
    }

    // 회원 검색
    if (popupSearchBtn) {
        popupSearchBtn.addEventListener('click', function () {
            searchMembers(1);
        });
    }

    // 회원 검색 엔터키
    const popupSearchTerm = document.getElementById('popup_search_term');
    if (popupSearchTerm) {
        popupSearchTerm.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                searchMembers(1);
            }
        });
    }

    // 회원 선택 확인
    if (popupMemberConfirm) {
        popupMemberConfirm.addEventListener('click', function () {
            if (selectedMember) {
                applySelectedMember();
            }
        });
    }

    // 회원 모달 닫기
    if (memberSearchCancelButtons.length) {
        memberSearchCancelButtons.forEach(btn => btn.addEventListener('click', closeMemberSearchModal));
    }
    if (memberModalClose) {
        memberModalClose.addEventListener('click', closeMemberSearchModal);
    }

    // 프로그램 검색 모달 열기
    if (programSearchBtn) {
        programSearchBtn.addEventListener('click', function () {
            openProgramSearchModal();
        });
    }

    // 프로그램 검색
    if (popupProgramSearchBtn) {
        popupProgramSearchBtn.addEventListener('click', function () {
            searchPrograms(1);
        });
    }

    // 프로그램 검색 엔터키
    const popupProgramKeyword = document.getElementById('popup_program_keyword');
    if (popupProgramKeyword) {
        popupProgramKeyword.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                searchPrograms(1);
            }
        });
    }

    // 직접 입력
    programDirectInput?.addEventListener('input', () => {
        selectedProgram = null;
        updateProgramConfirmButton();
        updateProgramDirectInputState();
    });
    programDirectConfirmButton?.addEventListener('click', applyProgramDirectInput);

    // 프로그램 선택 확인
    if (popupProgramConfirm) {
        popupProgramConfirm.addEventListener('click', function () {
            if (selectedProgram) {
                applySelectedProgram();
            }
        });
    }

    // 프로그램 모달 닫기
    programSearchCancel.forEach(btn => btn.addEventListener('click', closeProgramSearchModal));
    if (programModalClose) {
        programModalClose.addEventListener('click', closeProgramSearchModal);
    }

    updateProgramDirectInputState();
});

// 회원 검색 모달 열기
function openMemberSearchModal() {
    const modal = document.getElementById('member-search-modal');
    if (modal) {
        modal.style.display = 'flex';
        selectedMember = null;
        currentMemberPage = 1;
        updateMemberConfirmButton();
        searchMembers(1);
    }
}

// 회원 검색 모달 닫기
function closeMemberSearchModal() {
    const modal = document.getElementById('member-search-modal');
    if (modal) {
        modal.style.display = 'none';
        selectedMember = null;
    }
}

// 회원 검색
function searchMembers(page) {
    currentMemberPage = page;
    const form = document.getElementById('member-search-form');
    const formData = new FormData(form);
    const params = new URLSearchParams(formData);
    params.append('page', page);

    fetch(`${memberSearchUrl}?${params.toString()}`)
        .then(response => response.json())
        .then(data => {
            renderMemberList(data.members, data.pagination);
            renderMemberPagination(data.pagination);
        })
        .catch(error => {
            console.error('회원 검색 오류:', error);
            alert('회원 검색 중 오류가 발생했습니다.');
        });
}

// 회원 목록 렌더링
function renderMemberList(members, pagination) {
    const tbody = document.getElementById('popup-member-list-body');
    if (!tbody) return;

    const selectionMode = tbody.dataset.selectionMode || 'single';
    const total = pagination ? pagination.total : members.length;
    const perPage = pagination ? pagination.per_page : members.length;
    const current = pagination ? pagination.current_page : 1;
    const baseNumber = total - ((current - 1) * perPage);

    if (!members || members.length === 0) {
        const emptyCols = selectionMode === 'multiple' ? 6 : 5;
        tbody.innerHTML = `<tr><td colspan="${emptyCols}" class="text-center">검색 결과가 없습니다.</td></tr>`;
        return;
    }

    if (selectionMode === 'single') {
        tbody.innerHTML = members.map((member, index) => {
            const memberData = encodeURIComponent(JSON.stringify(member));
            const rowNumber = Math.max(1, baseNumber - index);

            return `
                <tr>
                    <td>${rowNumber}</td>
                    <td>${member.login_id || '-'}</td>
                    <td>${member.name}</td>
                    <td>${member.school_name || '-'}</td>
                    <td>
                        <button type="button" class="btn btn-primary btn-sm member-select-btn" data-member="${memberData}">
                            선택
                        </button>
                    </td>
                </tr>
            `;
        }).join('');

        tbody.querySelectorAll('.member-select-btn').forEach((button) => {
            button.addEventListener('click', function () {
                selectedMember = JSON.parse(decodeURIComponent(this.dataset.member));
                applySelectedMember();
            });
        });

        updateMemberConfirmButton();
        return;
    }

    const inputName = 'member_checkbox';
    tbody.innerHTML = members.map((member, index) => {
        const memberData = encodeURIComponent(JSON.stringify(member));
        const rowNumber = Math.max(1, baseNumber - index);

        return `
            <tr>
                <td>
                    <input type="checkbox" name="${inputName}" value="${member.id}" data-member="${memberData}" class="popup-member-checkbox">
                </td>
                <td>${rowNumber}</td>
                <td>${member.login_id || '-'}</td>
                <td>${member.name}</td>
                <td>${member.school_name || '-'}</td>
                <td>${member.email || '-'}</td>
            </tr>
        `;
    }).join('');

    tbody.querySelectorAll(`input[name="${inputName}"]`).forEach((input) => {
        input.addEventListener('change', function () {
            const checkedMembers = Array.from(tbody.querySelectorAll(`input[name="${inputName}"]:checked`))
                .map((checkbox) => JSON.parse(decodeURIComponent(checkbox.dataset.member)));
            selectedMember = checkedMembers.length > 0 ? checkedMembers : null;
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
            selectedMember = null;
            updateMemberConfirmButton();
            searchMembers(targetPage);
        });
    });
}

// 회원 확인 버튼 상태 업데이트
function updateMemberConfirmButton() {
    const confirmBtn = document.getElementById('popup-member-confirm') || document.getElementById('popup-add-btn');
    if (confirmBtn) {
        confirmBtn.disabled = !selectedMember;
    }
}

// 선택한 회원 적용
function applySelectedMember() {
    if (!selectedMember) return;

    const nameInput = document.getElementById('applicant_name');
    if (nameInput) {
        nameInput.value = selectedMember.name || '';
    }

    const memberIdInput = document.getElementById('member_id');
    if (memberIdInput) {
        memberIdInput.value = selectedMember.id || '';
    }

    const schoolNameInput = document.getElementById('school_name');
    if (schoolNameInput) {
        schoolNameInput.value = selectedMember.school_name || '';
    }

    const schoolLevelInput = document.getElementById('school_level');
    if (schoolLevelInput) {
        schoolLevelInput.value = selectedMember.school_level_label || selectedMember.school_level || '';
    }

    const schoolIdInput = document.getElementById('school_id');
    if (schoolIdInput) {
        schoolIdInput.value = selectedMember.school_id || '';
    }

    const gradeHidden = document.getElementById('applicant_grade');
    const gradeDisplay = document.getElementById('applicant_grade_display');
    if (gradeHidden) {
        gradeHidden.value = selectedMember.grade || '';
    }
    if (gradeDisplay) {
        gradeDisplay.value = selectedMember.grade ? `${selectedMember.grade}학년` : '';
    }

    const classHidden = document.getElementById('applicant_class');
    const classDisplay = document.getElementById('applicant_class_display');
    if (classHidden) {
        classHidden.value = selectedMember.class_number || '';
    }
    if (classDisplay) {
        classDisplay.value = selectedMember.class_number ? `${selectedMember.class_number}반` : '';
    }

    const contactInput = document.getElementById('applicant_contact');
    if (contactInput) {
        contactInput.value = selectedMember.contact || '';
    }

    const guardianInput = document.getElementById('guardian_contact');
    if (guardianInput) {
        guardianInput.value = selectedMember.parent_contact || '';
    }

    closeMemberSearchModal();
}

// 프로그램 검색 모달 열기
function openProgramSearchModal() {
    const modal = document.getElementById('program-search-modal');
    if (modal) {
        const modalContent = modal.querySelector('.category-modal-content');
        if (modalContent) {
            modalContent.scrollTop = 0;
        }

        const closeButton = modal.querySelector('.category-modal-close');
        if (closeButton && !closeButton.querySelector('i')) {
            const icon = document.createElement('i');
            icon.className = 'fas fa-times';
            closeButton.appendChild(icon);
        }

        const directInput = document.getElementById('popup_direct_program_name');
        if (directInput) {
            directInput.value = '';
        }

        modal.style.display = 'flex';
        selectedProgram = null;
        currentProgramPage = 1;
        updateProgramConfirmButton();
        updateProgramDirectInputState();
        searchPrograms(1);
    }
}

// 프로그램 검색 모달 닫기
function closeProgramSearchModal() {
    const modal = document.getElementById('program-search-modal');
    if (modal) {
        modal.style.display = 'none';
        selectedProgram = null;
    }
}

// 프로그램 검색
function searchPrograms(page) {
    currentProgramPage = page;
    const form = document.getElementById('program-search-form');
    const formData = new FormData(form);
    const params = new URLSearchParams(formData);
    params.append('page', page);

    fetch(`${programSearchUrl}?${params.toString()}`)
        .then(response => response.json())
        .then(data => {
            renderProgramList(data.programs, data.pagination);
            renderProgramPagination(data.pagination);
            updateProgramDirectInputState();
        })
        .catch(error => {
            console.error('프로그램 검색 오류:', error);
            alert('프로그램 검색 중 오류가 발생했습니다.');
            updateProgramDirectInputState();
        });
}

// 프로그램 목록 렌더링
function renderProgramList(programs, pagination) {
    const tbody = document.getElementById('popup-program-list-body');
    if (!tbody) return;

    const searchMode = document.getElementById('program-search-modal')?.dataset.programSearchMode || 'reservation';

    if (!programs || programs.length === 0) {
        const emptyCols = searchMode === 'reservation' ? 6 : 3;
        tbody.innerHTML = `<tr><td colspan="${emptyCols}" class="text-center">검색 결과가 없습니다.</td></tr>`;
        return;
    }

    const educationTypeLabels = {
        'middle_semester': '중등학기',
        'middle_vacation': '중등방학',
        'high_semester': '고등학기',
        'high_vacation': '고등방학',
        'special': '특별프로그램'
    };

    const total = pagination ? pagination.total : programs.length;
    const perPage = pagination ? pagination.per_page : programs.length;
    const current = pagination ? pagination.current_page : 1;
    const baseNumber = total - ((current - 1) * perPage);

    if (searchMode === 'reservation') {
        tbody.innerHTML = programs.map((program, index) => {
            const displayDate = program.education_start_date ? program.education_start_date.replace(/-/g, '.') : '-';
            const displayFee = program.education_fee ? Number(program.education_fee).toLocaleString() + '원' : '무료';
            const programData = encodeURIComponent(JSON.stringify(program));
            const rowNumber = Math.max(1, baseNumber - index);

            return `
                <tr>
                    <td>${rowNumber}</td>
                    <td>${program.program_name}</td>
                    <td>${educationTypeLabels[program.education_type] || program.education_type || '-'}</td>
                    <td>${displayDate}</td>
                    <td>${displayFee}</td>
                    <td>
                        <button type="button" class="btn btn-primary btn-sm program-select-btn" data-program="${programData}">
                            선택
                        </button>
                    </td>
                </tr>
            `;
        }).join('');

        tbody.querySelectorAll('.program-select-btn').forEach((button) => {
            button.addEventListener('click', function () {
                selectedProgram = JSON.parse(decodeURIComponent(this.dataset.program));
                applySelectedProgram();
            });
        });

        updateProgramConfirmButton();
        return;
    }

    // name 모드
    tbody.innerHTML = programs.map((program, index) => {
        const programData = encodeURIComponent(JSON.stringify(program));
        const rowNumber = Math.max(1, baseNumber - index);

        return `
            <tr>
                <td>${rowNumber}</td>
                <td>${program.program_name}</td>
                <td>
                    <button type="button" class="btn btn-primary btn-sm program-select-btn" data-program="${programData}">
                        선택
                    </button>
                </td>
            </tr>
        `;
    }).join('');

    tbody.querySelectorAll('.program-select-btn').forEach((button) => {
        button.addEventListener('click', function () {
            selectedProgram = JSON.parse(decodeURIComponent(this.dataset.program));
            applySelectedProgram();
        });
    });
}

// 프로그램 페이지네이션 렌더링
function renderProgramPagination(pagination) {
    const container = document.getElementById('popup-program-pagination');
    if (!container) return;

    const lastPage = Math.max(1, pagination?.last_page ?? 1);
    const current = Math.min(lastPage, pagination?.current_page ?? currentProgramPage);
    const startPage = Math.max(1, current - 2);
    const endPage = Math.min(lastPage, current + 2);

    let html = '<nav aria-label="페이지 네비게이션"><ul class="pagination">';

    const pageItem = (page, label, disabled = false) => {
        if (disabled) {
            return `<li class="page-item disabled"><span class="page-link">${label}</span></li>`;
        }
        return `<li class="page-item"><a href="#" class="page-link" data-program-page="${page}">${label}</a></li>`;
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

    container.querySelectorAll('a[data-program-page]').forEach((link) => {
        link.addEventListener('click', function (event) {
            event.preventDefault();
            const targetPage = Number(this.dataset.programPage);
            if (!targetPage || targetPage === currentProgramPage) {
                return;
            }
            selectedProgram = null;
            updateProgramConfirmButton();
            searchPrograms(targetPage);
        });
    });
}

// 프로그램 확인 버튼 상태 업데이트
function updateProgramConfirmButton() {
    const confirmBtn = document.getElementById('popup-program-confirm');
    if (confirmBtn) {
        confirmBtn.disabled = !selectedProgram;
    }
}

function updateProgramDirectInputState() {
    const directInput = document.getElementById('popup_direct_program_name');
    const fallbackInput = document.getElementById('popup_program_keyword') || document.getElementById('popup_search_keyword');
    const directConfirmButton = document.getElementById('popup-confirm-btn');

    if (directConfirmButton) {
        const hasValue = directInput && directInput.value.trim().length > 0;
        directConfirmButton.disabled = !hasValue;
    }

    if (directInput) {
        focusElementWithoutScroll(directInput, '#program-search-modal .category-modal-content');
    } else if (fallbackInput) {
        focusElementWithoutScroll(fallbackInput, '#program-search-modal .category-modal-content');
    }
}

function applyProgramDirectInput() {
    const directInput = document.getElementById('popup_direct_program_name');
    if (!directInput) {
        return;
    }

    const programName = directInput.value.trim();
    if (!programName) {
        alert('프로그램명을 입력해주세요.');
        focusElementWithoutScroll(directInput, '#program-search-modal .category-modal-content');
        return;
    }

    const programNameInput = document.getElementById('program_name');
    if (programNameInput) {
        programNameInput.value = programName;
    }

    const programIdInput = document.getElementById('program_reservation_id');
    if (programIdInput) {
        programIdInput.value = '';
    }

    selectedProgram = null;
    updateProgramConfirmButton();
    closeProgramSearchModal();
}

// 선택한 프로그램 적용
function applySelectedProgram() {
    if (!selectedProgram) return;

    const programIdInput = document.getElementById('program_reservation_id');
    if (programIdInput) {
        programIdInput.value = selectedProgram.id || '';
    }

    const programNameInput = document.getElementById('program_name');
    if (programNameInput) {
        programNameInput.value = selectedProgram.program_name || '';
    }

    const participationDateInput = document.getElementById('participation_date');
    const participationDateDisplay = document.getElementById('participation_date_display');
    if (participationDateInput) {
        participationDateInput.value = selectedProgram.education_start_date || '';
    }
    if (participationDateDisplay) {
        participationDateDisplay.value = selectedProgram.education_start_date
            ? selectedProgram.education_start_date.replace(/-/g, '.')
            : '';
    }

    const participationFeeInput = document.getElementById('participation_fee');
    if (participationFeeInput) {
        participationFeeInput.value = typeof selectedProgram.education_fee !== 'undefined' && selectedProgram.education_fee !== null
            ? selectedProgram.education_fee
            : '';
    }

    const participationFeeDisplay = document.getElementById('participation_fee_display');
    if (participationFeeDisplay) {
        participationFeeDisplay.value = typeof selectedProgram.education_fee !== 'undefined' && selectedProgram.education_fee !== null
            ? Number(selectedProgram.education_fee).toLocaleString()
            : '';
    }

    const educationRadio = document.querySelector(`input[name="education_type"][value="${selectedProgram.education_type}"]`);
    if (educationRadio) {
        educationRadio.checked = true;
    }

    if (Array.isArray(selectedProgram.payment_methods)) {
        document.querySelectorAll('input[name="payment_methods[]"]').forEach((checkbox) => {
            checkbox.checked = selectedProgram.payment_methods.includes(checkbox.value);
        });
    }

    closeProgramSearchModal();
}

function focusElementWithoutScroll(element, containerSelector) {
    if (!element) {
        return;
    }

    const container = containerSelector ? document.querySelector(containerSelector) : null;

    if (typeof element.focus === 'function') {
        try {
            element.focus({ preventScroll: true });
            return;
        } catch (error) {
            // preventScroll 미지원 브라우저
        }
    }

    const previousScroll = container
        ? { top: container.scrollTop, left: container.scrollLeft }
        : { top: window.scrollY, left: window.scrollX };

    element.focus();

    if (container) {
        container.scrollTop = previousScroll.top;
        container.scrollLeft = previousScroll.left;
    } else {
        window.scrollTo(previousScroll.left, previousScroll.top);
    }
}


/**
 * 개인 신청 내역 검색 모달 제어 스크립트
 */

let currentMemberPage = 1;
let currentProgramPage = 1;
let selectedMember = null;
let selectedProgram = null;

document.addEventListener('DOMContentLoaded', function () {
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
    const programSearchModal = document.getElementById('program-search-modal');
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

    fetch(`/backoffice/individual-applications/search-members?${params.toString()}`)
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

    if (!members || members.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center">검색 결과가 없습니다.</td></tr>';
        return;
    }

    const selectionMode = tbody.dataset.selectionMode || 'single';
    const startingNo = pagination ? pagination.total - ((pagination.current_page - 1) * pagination.per_page) : members.length;
    const inputName = selectionMode === 'single' ? 'member_radio' : 'member_checkbox';
    const inputType = selectionMode === 'single' ? 'radio' : 'checkbox';

    tbody.innerHTML = members.map((member, index) => {
        const memberData = encodeURIComponent(JSON.stringify(member));
        return `
        <tr>
            <td>
                <input type="${inputType}" name="${inputName}" value="${member.id}" data-member="${memberData}">
            </td>
            <td>${startingNo - index}</td>
            <td>${member.login_id || '-'}</td>
            <td>${member.name}</td>
            <td>${member.school_name || '-'}</td>
            <td>${member.email || '-'}</td>
        </tr>
    `;
    }).join('');

    const selector = inputType === 'radio' ? `input[name="${inputName}"]` : `input[name="${inputName}"]`;
    tbody.querySelectorAll(selector).forEach(input => {
        input.addEventListener('change', function () {
            selectedMember = JSON.parse(decodeURIComponent(this.dataset.member));
            updateMemberConfirmButton();
        });
    });
}

// 회원 페이지네이션 렌더링
function renderMemberPagination(pagination) {
    const container = document.getElementById('popup-pagination');
    if (!container) return;

    if (pagination.last_page <= 1) {
        container.innerHTML = '';
        return;
    }

    let html = '<div class="pagination">';

    if (pagination.current_page > 1) {
        html += `<a href="#" onclick="searchMembers(${pagination.current_page - 1}); return false;">&lt;</a>`;
    } else {
        html += '<span class="disabled">&lt;</span>';
    }

    for (let i = 1; i <= pagination.last_page; i++) {
        if (i === pagination.current_page) {
            html += `<span class="active">${i}</span>`;
        } else {
            html += `<a href="#" onclick="searchMembers(${i}); return false;">${i}</a>`;
        }
    }

    if (pagination.current_page < pagination.last_page) {
        html += `<a href="#" onclick="searchMembers(${pagination.current_page + 1}); return false;">&gt;</a>`;
    } else {
        html += '<span class="disabled">&gt;</span>';
    }

    html += '</div>';
    container.innerHTML = html;
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
        modal.style.display = 'flex';
        selectedProgram = null;
        currentProgramPage = 1;
        updateProgramConfirmButton();
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

    fetch(`/backoffice/individual-applications/search-programs?${params.toString()}`)
        .then(response => response.json())
        .then(data => {
            renderProgramList(data.programs, data.pagination);
            renderProgramPagination(data.pagination);
        })
        .catch(error => {
            console.error('프로그램 검색 오류:', error);
            alert('프로그램 검색 중 오류가 발생했습니다.');
        });
}

// 프로그램 목록 렌더링
function renderProgramList(programs, pagination) {
    const tbody = document.getElementById('popup-program-list-body');
    if (!tbody) return;

    if (programs.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center">검색 결과가 없습니다.</td></tr>';
        return;
    }

    const educationTypeLabels = {
        'middle_semester': '중등학기',
        'middle_vacation': '중등방학',
        'high_semester': '고등학기',
        'high_vacation': '고등방학',
        'special': '특별프로그램'
    };

    const startingNo = pagination ? pagination.total - ((pagination.current_page - 1) * pagination.per_page) : programs.length;

    tbody.innerHTML = programs.map((program, index) => {
        const displayDate = program.education_start_date ? program.education_start_date.replace(/-/g, '.') : '-';
        const displayFee = program.education_fee ? Number(program.education_fee).toLocaleString() + '원' : '무료';
        const programData = encodeURIComponent(JSON.stringify(program));

        return `
        <tr>
            <td>
                <input type="radio" name="program_radio" value="${program.id}" data-program="${programData}">
            </td>
            <td>${startingNo - index}</td>
            <td>${educationTypeLabels[program.education_type] || program.education_type}</td>
            <td>${program.program_name}</td>
            <td>${displayDate}</td>
            <td>${displayFee}</td>
        </tr>
    `;
    }).join('');

    // 라디오 버튼 이벤트
    tbody.querySelectorAll('input[name="program_radio"]').forEach(radio => {
        radio.addEventListener('change', function () {
            selectedProgram = JSON.parse(decodeURIComponent(this.dataset.program));
            updateProgramConfirmButton();
        });
    });
}

// 프로그램 페이지네이션 렌더링
function renderProgramPagination(pagination) {
    const container = document.getElementById('popup-program-pagination');
    if (!container) return;

    if (pagination.last_page <= 1) {
        container.innerHTML = '';
        return;
    }

    let html = '<div class="pagination">';

    if (pagination.current_page > 1) {
        html += `<a href="#" onclick="searchPrograms(${pagination.current_page - 1}); return false;">&lt;</a>`;
    } else {
        html += '<span class="disabled">&lt;</span>';
    }

    for (let i = 1; i <= pagination.last_page; i++) {
        if (i === pagination.current_page) {
            html += `<span class="active">${i}</span>`;
        } else {
            html += `<a href="#" onclick="searchPrograms(${i}); return false;">${i}</a>`;
        }
    }

    if (pagination.current_page < pagination.last_page) {
        html += `<a href="#" onclick="searchPrograms(${pagination.current_page + 1}); return false;">&gt;</a>`;
    } else {
        html += '<span class="disabled">&gt;</span>';
    }

    html += '</div>';
    container.innerHTML = html;
}

// 프로그램 확인 버튼 상태 업데이트
function updateProgramConfirmButton() {
    const confirmBtn = document.getElementById('popup-program-confirm');
    if (confirmBtn) {
        confirmBtn.disabled = !selectedProgram;
    }
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

    closeProgramSearchModal();
}


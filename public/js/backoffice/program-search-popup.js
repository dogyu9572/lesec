/**
 * 백오피스 프로그램 검색 팝업 제어 스크립트
 */

let currentProgramPage = 1;
let selectedProgram = null;
let programSearchUrl = '/backoffice/individual-applications/search-programs';

document.addEventListener('DOMContentLoaded', function () {
    const searchForm = document.getElementById('program-search-form');
    if (searchForm && searchForm.dataset.programSearchUrl) {
        programSearchUrl = searchForm.dataset.programSearchUrl;
    }

    const mode = searchForm?.dataset.programSearchMode || 'reservation';
    const programDirectInput = document.getElementById('popup_direct_program_name');
    const programDirectConfirmButton = document.getElementById('popup-confirm-btn');
    const popupProgramSearchBtn = document.getElementById('popup-program-search-btn');
    const popupProgramConfirm = document.getElementById('popup-program-confirm');
    const popupProgramKeyword = document.getElementById('popup_program_keyword');

    // 페이지 로드 시 기본 검색 실행
    searchPrograms(1);

    // 프로그램 검색
    if (popupProgramSearchBtn) {
        popupProgramSearchBtn.addEventListener('click', function () {
            searchPrograms(1);
        });
    }

    // 프로그램 검색 엔터키
    if (popupProgramKeyword) {
        popupProgramKeyword.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                searchPrograms(1);
            }
        });
    }

    // 직접 입력
    if (programDirectInput) {
        programDirectInput.addEventListener('input', () => {
            selectedProgram = null;
            updateProgramConfirmButton();
            updateProgramDirectInputState();
        });
    }

    if (programDirectConfirmButton) {
        programDirectConfirmButton.addEventListener('click', applyProgramDirectInput);
    }

    // 프로그램 선택 확인
    if (popupProgramConfirm) {
        popupProgramConfirm.addEventListener('click', function () {
            if (selectedProgram) {
                applySelectedProgram();
            }
        });
    }

    updateProgramDirectInputState();
});

// 프로그램 검색
function searchPrograms(page) {
    currentProgramPage = page;
    const form = document.getElementById('program-search-form');
    if (!form) return;

    const formData = new FormData(form);
    const params = new URLSearchParams(formData);
    params.append('page', page);

    const tbody = document.getElementById('popup-program-list-body');
    if (tbody) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center">프로그램 정보를 불러오는 중입니다...</td></tr>';
    }

    fetch(`${programSearchUrl}?${params.toString()}`)
        .then(response => response.json())
        .then(data => {
            renderProgramList(data.programs, data.pagination);
            renderProgramPagination(data.pagination);
            updateProgramDirectInputState();
        })
        .catch(error => {
            console.error('프로그램 검색 오류:', error);
            const tbody = document.getElementById('popup-program-list-body');
            if (tbody) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">프로그램 검색 중 오류가 발생했습니다.</td></tr>';
            }
            updateProgramDirectInputState();
        });
}

// 프로그램 목록 렌더링
function renderProgramList(programs, pagination) {
    const tbody = document.getElementById('popup-program-list-body');
    if (!tbody) return;

    const searchForm = document.getElementById('program-search-form');
    const searchMode = searchForm?.dataset.programSearchMode || 'reservation';

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
    const directConfirmButton = document.getElementById('popup-confirm-btn');

    if (directConfirmButton) {
        const hasValue = directInput && directInput.value.trim().length > 0;
        directConfirmButton.disabled = !hasValue;
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
        return;
    }

    // 부모창으로 직접 입력값 전달
    if (window.opener && typeof window.opener.applySelectedProgram === 'function') {
        window.opener.applySelectedProgram({
            id: '',
            name: programName,
        });
        window.close();
    } else {
        alert('부모창과의 연결이 끊어졌습니다.');
    }
}

// 선택한 프로그램 적용 (부모창으로 전달)
function applySelectedProgram() {
    if (!selectedProgram) return;

    // 부모창으로 데이터 전달
    if (window.opener && typeof window.opener.applySelectedProgram === 'function') {
        window.opener.applySelectedProgram(selectedProgram);
        window.close();
    } else {
        alert('부모창과의 연결이 끊어졌습니다.');
    }
}


/**
 * 단체 프로그램 폼 JavaScript
 */

let currentPage = 1;
let programSearchUrl = '';
let directInputTargetInputId = 'program_name';

document.addEventListener('DOMContentLoaded', function() {
    // 제한없음 체크박스 처리
    const unlimitedCapacityCheckbox = document.getElementById('is_unlimited_capacity');
    const capacityInput = document.getElementById('capacity');
    
    if (unlimitedCapacityCheckbox && capacityInput) {
        unlimitedCapacityCheckbox.addEventListener('change', function() {
            if (this.checked) {
                capacityInput.disabled = true;
                capacityInput.value = '';
            } else {
                capacityInput.disabled = false;
            }
        });
    }

    // 무료 체크박스 처리
    const freeCheckbox = document.getElementById('is_free');
    const educationFeeInput = document.getElementById('education_fee');
    
    if (freeCheckbox && educationFeeInput) {
        freeCheckbox.addEventListener('change', function() {
            if (this.checked) {
                educationFeeInput.disabled = true;
                educationFeeInput.value = '';
            } else {
                educationFeeInput.disabled = false;
            }
        });
    }

    // 프로그램명 검색 버튼
    const searchProgramBtn = document.getElementById('searchProgramBtn');
    if (searchProgramBtn) {
        searchProgramBtn.addEventListener('click', function() {
            openProgramSearchModal();
        });
    }

    // 모달 내 검색 버튼
    const popupSearchBtn = document.getElementById('popup-program-search-btn') || document.getElementById('popup-search-btn');
    if (popupSearchBtn) {
        popupSearchBtn.addEventListener('click', function() {
            searchPrograms(1);
        });
    }

    // 모달 내 검색어 입력 시 엔터키 처리
    const popupSearchKeyword = document.getElementById('popup_program_keyword') || document.getElementById('popup_direct_program_name') || document.getElementById('popup_search_keyword');
    if (popupSearchKeyword) {
        popupSearchKeyword.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                searchPrograms(1);
            }
        });
        popupSearchKeyword.addEventListener('input', updateDirectInputButtonState);
    }

    // 확인 버튼 (프로그램명이 없을 경우 직접 입력)
    const popupConfirmBtn = document.getElementById('popup-confirm-btn');
    if (popupConfirmBtn) {
        popupConfirmBtn.addEventListener('click', applyDirectInputProgramName);
    }

    // 날짜 입력 필드 초기화 (showPicker 사용 - 클릭 시에만)
    const dateInputs = document.querySelectorAll('input[type="date"]');
    dateInputs.forEach(input => {
        input.addEventListener('click', function(e) {
            // 이미 열려있는 경우 중복 호출 방지
            if (this.showPicker && document.activeElement === this) {
                try {
                    this.showPicker();
                } catch (error) {
                    // showPicker가 실패해도 정상 동작 (브라우저 기본 동작 사용)
                    console.debug('showPicker not available:', error.message);
                }
            }
        });
    });

    // 폼 제출 시 disabled 필드 처리
    const form = document.querySelector('form[action*="group-programs"]');
    if (form) {
        form.addEventListener('submit', function(e) {
            // 제한없음이 체크되어 있으면 capacity 필드를 제출에 포함 (disabled 해제)
            const unlimitedCapacityCheckbox = document.getElementById('is_unlimited_capacity');
            const capacityInput = document.getElementById('capacity');
            if (unlimitedCapacityCheckbox && unlimitedCapacityCheckbox.checked && capacityInput) {
                capacityInput.disabled = false;
                // 빈 값으로 설정 (서버에서 null 처리)
                if (!capacityInput.value) {
                    capacityInput.value = '';
                }
            }

            // 무료가 체크되어 있으면 education_fee 필드를 제출에 포함 (disabled 해제)
            const freeCheckbox = document.getElementById('is_free');
            const educationFeeInput = document.getElementById('education_fee');
            if (freeCheckbox && freeCheckbox.checked && educationFeeInput) {
                educationFeeInput.disabled = false;
                // 빈 값으로 설정 (서버에서 null 처리)
                if (!educationFeeInput.value) {
                    educationFeeInput.value = '';
                }
            }
        }, false);
    }
    const modal = document.getElementById('program-search-modal');
    if (modal) {
        programSearchUrl = modal.dataset.programSearchUrl || '/backoffice/group-programs/search-programs';
        directInputTargetInputId = modal.dataset.programInputId || 'program_name';
    } else {
        programSearchUrl = '/backoffice/group-programs/search-programs';
        directInputTargetInputId = 'program_name';
    }

    updateDirectInputButtonState();
});

/**
 * 프로그램명 검색 모달 열기
 */
function openProgramSearchModal() {
    const modal = document.getElementById('program-search-modal');
    if (modal) {
        const closeButton = modal.querySelector('.category-modal-close');
        if (closeButton) {
            let icon = closeButton.querySelector('i');
            if (!icon) {
                icon = document.createElement('i');
                closeButton.appendChild(icon);
            }
            icon.className = 'fas fa-times';
        }

        modal.style.display = 'flex';
        const modalContent = modal.querySelector('.category-modal-content');
        if (modalContent) {
            modalContent.scrollTop = 0;
        }
        currentPage = 1;
        updateDirectInputButtonState();
        searchPrograms(1);
    }
}

/**
 * 프로그램명 검색 모달 닫기
 */
function closeProgramSearchModal() {
    const modal = document.getElementById('program-search-modal');
    if (modal) {
        modal.style.display = 'none';
        currentPage = 1;
    }
}

/**
 * 프로그램 검색
 */
function searchPrograms(page = 1) {
    currentPage = page;
    const educationTypeSelect = document.getElementById('popup_education_type');
    const keywordInput = document.getElementById('popup_program_keyword') || document.getElementById('popup_search_keyword');
    const educationType = educationTypeSelect ? educationTypeSelect.value : '';
    const searchKeyword = keywordInput ? keywordInput.value : '';
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    const params = new URLSearchParams({
        page: page,
        education_type: educationType,
        search_keyword: searchKeyword,
        per_page: 10
    });

    fetch(`${programSearchUrl}?${params.toString()}`, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.programs) {
            renderProgramList(data.programs, data.pagination);
        } else {
            console.error('검색 실패:', data.message || '알 수 없는 오류');
            document.getElementById('popup-program-list-body').innerHTML = 
                '<tr><td colspan="3" class="text-center">검색 중 오류가 발생했습니다.</td></tr>';
        }
        updateDirectInputButtonState();
    })
    .catch(error => {
        console.error('Error:', error);
        const tbody = document.getElementById('popup-program-list-body');
        if (tbody) {
            tbody.innerHTML =
            '<tr><td colspan="3" class="text-center">검색 중 오류가 발생했습니다.</td></tr>';
        }
        updateDirectInputButtonState();
    });
}

/**
 * 프로그램 목록 렌더링
 */
function renderProgramList(programs, pagination) {
    const tbody = document.getElementById('popup-program-list-body');
    
    if (!programs || programs.length === 0) {
        tbody.innerHTML = '<tr><td colspan="3" class="text-center">검색 결과가 없습니다.</td></tr>';
        const paginationContainer = document.getElementById('popup-program-pagination') || document.getElementById('popup-pagination');
        if (paginationContainer) {
            paginationContainer.innerHTML = '';
        }
        return;
    }

    let html = '';
    programs.forEach((program, index) => {
        const number = pagination.total - (pagination.current_page - 1) * pagination.per_page - index;
        html += `
            <tr>
                <td>${number}</td>
                <td>${program.program_name}</td>
                <td>
                    <button type="button" class="btn btn-primary btn-sm" onclick="selectProgramName('${program.program_name.replace(/'/g, "\\'")}')">
                        선택
                    </button>
                </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
    renderPagination(pagination);
}

/**
 * 페이지네이션 렌더링
 */
function renderPagination(pagination) {
    const paginationContainer = document.getElementById('popup-program-pagination') || document.getElementById('popup-pagination');
    
    if (!paginationContainer) {
        return;
    }

    let html = '<nav aria-label="페이지 네비게이션"><ul class="pagination">';

    if (pagination.current_page > 1) {
        html += `<li class="page-item"><a href="#" class="page-link" onclick="searchPrograms(1); return false;"><i class="fas fa-angle-double-left"></i></a></li>`;
        html += `<li class="page-item"><a href="#" class="page-link" onclick="searchPrograms(${pagination.current_page - 1}); return false;"><i class="fas fa-chevron-left"></i></a></li>`;
    } else {
        html += '<li class="page-item disabled"><span class="page-link"><i class="fas fa-angle-double-left"></i></span></li>';
        html += '<li class="page-item disabled"><span class="page-link"><i class="fas fa-chevron-left"></i></span></li>';
    }

    const startPage = Math.max(1, pagination.current_page - 2);
    const endPage = Math.min(pagination.last_page, pagination.current_page + 2);

    for (let i = startPage; i <= endPage; i++) {
        if (i === pagination.current_page) {
            html += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
        } else {
            html += `<li class="page-item"><a href="#" class="page-link" onclick="searchPrograms(${i}); return false;">${i}</a></li>`;
        }
    }

    if (pagination.current_page < pagination.last_page) {
        html += `<li class="page-item"><a href="#" class="page-link" onclick="searchPrograms(${pagination.current_page + 1}); return false;"><i class="fas fa-chevron-right"></i></a></li>`;
        html += `<li class="page-item"><a href="#" class="page-link" onclick="searchPrograms(${pagination.last_page}); return false;"><i class="fas fa-angle-double-right"></i></a></li>`;
    } else {
        html += '<li class="page-item disabled"><span class="page-link"><i class="fas fa-chevron-right"></i></span></li>';
        html += '<li class="page-item disabled"><span class="page-link"><i class="fas fa-angle-double-right"></i></span></li>';
    }

    html += '</ul></nav>';
    paginationContainer.innerHTML = html;
}

/**
 * 프로그램명 선택
 */
function selectProgramName(programName) {
    const programNameInput = document.getElementById(directInputTargetInputId);
    if (programNameInput) {
        programNameInput.value = programName;
        closeProgramSearchModal();
    }
}

function updateDirectInputButtonState() {
    const keywordField = document.getElementById('popup_direct_program_name') || document.getElementById('popup_program_keyword') || document.getElementById('popup_search_keyword');
    const popupConfirmBtn = document.getElementById('popup-confirm-btn');
    if (keywordField) {
        keywordField.disabled = false;
        keywordField.readOnly = false;
        focusInputWithoutScroll(keywordField, '#program-search-modal .category-modal-content');
    }
    if (popupConfirmBtn) {
        popupConfirmBtn.disabled = false;
    }
}

function applyDirectInputProgramName() {
    const keywordField = document.getElementById('popup_direct_program_name') || document.getElementById('popup_program_keyword') || document.getElementById('popup_search_keyword');
    const programName = keywordField ? keywordField.value.trim() : '';

    if (!programName) {
        alert('프로그램명을 입력해주세요.');
        return;
    }

    selectProgramName(programName);
}

function focusInputWithoutScroll(element, containerSelector) {
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

    const previousScroll = container ? { top: container.scrollTop, left: container.scrollLeft } : { top: window.scrollY, left: window.scrollX };

    element.focus();

    if (container) {
        container.scrollTop = previousScroll.top;
        container.scrollLeft = previousScroll.left;
    } else {
        window.scrollTo(previousScroll.left, previousScroll.top);
    }
}

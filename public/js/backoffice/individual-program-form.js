/**
 * 개인 프로그램 폼 JavaScript
 */

let currentPage = 1;

document.addEventListener('DOMContentLoaded', function() {
    // 신청유형 선택 시 조건부 필드 표시/숨김
    const receptionTypeSelect = document.getElementById('reception_type');
    const naverFormUrlGroup = document.getElementById('naver_form_url_group');
    const naverFormUrlInput = document.getElementById('naver_form_url');
    const waitlistUrlGroup = document.getElementById('waitlist_url_group');
    const waitlistUrlInput = document.getElementById('waitlist_url');
    
    function toggleConditionalFields() {
        const receptionType = receptionTypeSelect?.value;
        
        // 네이버폼 링크 필드 표시/숨김
        if (naverFormUrlGroup) {
            if (receptionType === 'naver_form') {
                naverFormUrlGroup.style.display = 'block';
                if (naverFormUrlInput) {
                    naverFormUrlInput.required = true;
                }
            } else {
                naverFormUrlGroup.style.display = 'none';
                if (naverFormUrlInput) {
                    naverFormUrlInput.required = false;
                    naverFormUrlInput.value = '';
                }
            }
        }
        
        // 대기자 신청 링크 필드 표시/숨김
        if (waitlistUrlGroup) {
            if (receptionType === 'first_come') {
                waitlistUrlGroup.style.display = 'block';
            } else {
                waitlistUrlGroup.style.display = 'none';
                if (waitlistUrlInput) {
                    waitlistUrlInput.value = '';
                }
            }
        }
        
        // 추첨 선택 시 제한없음 비활성화
        const unlimitedCapacityCheckbox = document.getElementById('is_unlimited_capacity');
        const capacityInput = document.getElementById('capacity');
        
        if (receptionType === 'lottery') {
            if (unlimitedCapacityCheckbox) {
                unlimitedCapacityCheckbox.checked = false;
                unlimitedCapacityCheckbox.disabled = true;
            }
            if (capacityInput) {
                capacityInput.disabled = false;
                if (!capacityInput.value) {
                    capacityInput.required = true;
                }
            }
        } else {
            if (unlimitedCapacityCheckbox) {
                unlimitedCapacityCheckbox.disabled = false;
            }
            if (capacityInput && !unlimitedCapacityCheckbox?.checked) {
                capacityInput.disabled = false;
                capacityInput.required = false;
            }
        }
    }
    
    // 초기 로드 시 조건부 필드 상태 설정
    toggleConditionalFields();
    
    // 신청유형 변경 시 조건부 필드 업데이트
    if (receptionTypeSelect) {
        receptionTypeSelect.addEventListener('change', toggleConditionalFields);
    }

    // 제한없음 체크박스 처리
    const unlimitedCapacityCheckbox = document.getElementById('is_unlimited_capacity');
    const capacityInput = document.getElementById('capacity');
    
    if (unlimitedCapacityCheckbox && capacityInput) {
        unlimitedCapacityCheckbox.addEventListener('change', function() {
            if (this.checked) {
                capacityInput.disabled = true;
                capacityInput.value = '';
                capacityInput.required = false;
            } else {
                capacityInput.disabled = false;
                // 추첨이 아니면 필수 아님
                if (receptionTypeSelect?.value !== 'lottery') {
                    capacityInput.required = false;
                }
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
    const popupSearchBtn = document.getElementById('popup-search-btn');
    if (popupSearchBtn) {
        popupSearchBtn.addEventListener('click', function() {
            searchPrograms(1);
        });
    }

    // 모달 내 검색어 입력 시 엔터키 처리
    const popupSearchKeyword = document.getElementById('popup_search_keyword');
    if (popupSearchKeyword) {
        popupSearchKeyword.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                searchPrograms(1);
            }
        });
    }

    // 확인 버튼 (프로그램명이 없을 경우 직접 입력)
    const popupConfirmBtn = document.getElementById('popup-confirm-btn');
    if (popupConfirmBtn) {
        popupConfirmBtn.addEventListener('click', function() {
            const searchKeyword = document.getElementById('popup_search_keyword').value.trim();
            if (searchKeyword) {
                selectProgramName(searchKeyword);
            } else {
                alert('프로그램명을 입력해주세요.');
            }
        });
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
    const form = document.querySelector('form[action*="individual-programs"]');
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
});

/**
 * 프로그램명 검색 모달 열기
 */
function openProgramSearchModal() {
    const modal = document.getElementById('program-search-modal');
    if (modal) {
        modal.style.display = 'flex';
        currentPage = 1;
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
    const educationType = document.getElementById('popup_education_type').value;
    const searchKeyword = document.getElementById('popup_search_keyword').value;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    const params = new URLSearchParams({
        page: page,
        education_type: educationType,
        search_keyword: searchKeyword,
        per_page: 10
    });

    fetch(`/backoffice/individual-programs/search-programs?${params.toString()}`, {
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
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('popup-program-list-body').innerHTML = 
            '<tr><td colspan="3" class="text-center">검색 중 오류가 발생했습니다.</td></tr>';
    });
}

/**
 * 프로그램 목록 렌더링
 */
function renderProgramList(programs, pagination) {
    const tbody = document.getElementById('popup-program-list-body');
    
    if (!programs || programs.length === 0) {
        tbody.innerHTML = '<tr><td colspan="3" class="text-center">검색 결과가 없습니다.</td></tr>';
        document.getElementById('popup-pagination').innerHTML = '';
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
    const paginationContainer = document.getElementById('popup-pagination');
    
    if (pagination.last_page <= 1) {
        paginationContainer.innerHTML = '';
        return;
    }

    let html = '<div class="pagination">';
    
    // 첫 페이지
    if (pagination.current_page > 1) {
        html += `<a href="#" class="page-link" onclick="searchPrograms(1); return false;">&laquo;</a>`;
        html += `<a href="#" class="page-link" onclick="searchPrograms(${pagination.current_page - 1}); return false;">&lsaquo;</a>`;
    }

    // 페이지 번호
    const startPage = Math.max(1, pagination.current_page - 2);
    const endPage = Math.min(pagination.last_page, pagination.current_page + 2);

    for (let i = startPage; i <= endPage; i++) {
        if (i === pagination.current_page) {
            html += `<span class="page-link active">${i}</span>`;
        } else {
            html += `<a href="#" class="page-link" onclick="searchPrograms(${i}); return false;">${i}</a>`;
        }
    }

    // 마지막 페이지
    if (pagination.current_page < pagination.last_page) {
        html += `<a href="#" class="page-link" onclick="searchPrograms(${pagination.current_page + 1}); return false;">&rsaquo;</a>`;
        html += `<a href="#" class="page-link" onclick="searchPrograms(${pagination.last_page}); return false;">&raquo;</a>`;
    }

    html += '</div>';
    paginationContainer.innerHTML = html;
}

/**
 * 프로그램명 선택
 */
function selectProgramName(programName) {
    const programNameInput = document.getElementById('program_name');
    if (programNameInput) {
        programNameInput.value = programName;
        closeProgramSearchModal();
    }
}


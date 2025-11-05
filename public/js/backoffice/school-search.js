/**
 * 학교 검색 JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    const searchButton = document.getElementById('school-search-btn');
    const searchModal = document.getElementById('school-search-modal');
    const searchForm = document.getElementById('school-search-form');
    const searchResults = document.getElementById('school-search-results');
    const schoolNameInput = document.getElementById('school_name');
    const schoolIdInput = document.getElementById('school_id');
    let currentPage = 1;

    // 검색 버튼 클릭 시 모달 열기
    if (searchButton) {
        searchButton.addEventListener('click', function() {
            if (searchModal) {
                searchModal.style.display = 'block';
                currentPage = 1;
                loadSchools();
            }
        });
    }

    // 모달 닫기
    const closeButtons = document.querySelectorAll('.close-modal, .modal-backdrop');
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            if (searchModal) {
                searchModal.style.display = 'none';
            }
        });
    });

    // 검색 폼 제출
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            currentPage = 1;
            loadSchools();
        });
    }

    // 학교 검색 AJAX
    function loadSchools() {
        if (!searchForm) return;

        const formData = new FormData(searchForm);
        formData.append('per_page', 10);
        formData.append('page', currentPage);

        const params = new URLSearchParams(formData);
        const searchUrl = searchForm.dataset.url || '/backoffice/schools/search';
        
        fetch(`${searchUrl}?${params.toString()}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.schools) {
                displaySchools(data.schools, data.pagination);
            } else {
                searchResults.innerHTML = '<tr><td colspan="5" class="text-center">검색 결과가 없습니다.</td></tr>';
            }
        })
        .catch(error => {
            console.error('학교 검색 오류:', error);
            searchResults.innerHTML = '<tr><td colspan="5" class="text-center text-danger">검색 중 오류가 발생했습니다.</td></tr>';
        });
    }

    // 검색 결과 표시
    function displaySchools(schools, pagination) {
        if (!searchResults) return;

        if (schools.length === 0) {
            searchResults.innerHTML = '<tr><td colspan="5" class="text-center">검색 결과가 없습니다.</td></tr>';
            return;
        }

        let html = '';
        schools.forEach(school => {
            html += `
                <tr class="school-row" data-school-id="${school.id}" data-school-name="${school.school_name}">
                    <td>${school.school_name || '-'}</td>
                    <td>${school.city || '-'}</td>
                    <td>${school.district || '-'}</td>
                    <td>${getSchoolLevelName(school.school_level)}</td>
                    <td>
                        <button type="button" class="btn btn-primary btn-sm select-school-btn">
                            선택
                        </button>
                    </td>
                </tr>
            `;
        });

        searchResults.innerHTML = html;

        // 선택 버튼 이벤트
        document.querySelectorAll('.select-school-btn').forEach(button => {
            button.addEventListener('click', function() {
                const row = this.closest('.school-row');
                const schoolId = row.dataset.schoolId;
                const schoolName = row.dataset.schoolName;

                if (schoolNameInput) {
                    schoolNameInput.value = schoolName;
                }
                if (schoolIdInput) {
                    schoolIdInput.value = schoolId;
                }

                if (searchModal) {
                    searchModal.style.display = 'none';
                }
            });
        });

        // 페이지네이션
        updatePagination(pagination);
    }

    // 학교급 한글명 반환
    function getSchoolLevelName(level) {
        const levels = {
            'elementary': '초등학교',
            'middle': '중학교',
            'high': '고등학교',
        };
        return levels[level] || '-';
    }

    // 페이지네이션 업데이트
    function updatePagination(pagination) {
        const paginationContainer = document.getElementById('school-search-pagination');
        if (!paginationContainer || !pagination) return;

        let html = '';
        
        if (pagination.current_page > 1) {
            html += `<button type="button" class="btn btn-sm btn-secondary" onclick="goToPage(${pagination.current_page - 1})">이전</button>`;
        }

        html += `<span class="mx-2">${pagination.current_page} / ${pagination.last_page}</span>`;

        if (pagination.current_page < pagination.last_page) {
            html += `<button type="button" class="btn btn-sm btn-secondary" onclick="goToPage(${pagination.current_page + 1})">다음</button>`;
        }

        paginationContainer.innerHTML = html;
    }

    // 페이지 이동
    window.goToPage = function(page) {
        currentPage = page;
        loadSchools();
    };
});


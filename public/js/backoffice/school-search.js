/**
 * 백오피스 학교 검색 모달 제어 스크립트
 */

document.addEventListener('DOMContentLoaded', function () {
    const searchButton = document.getElementById('school-search-btn');
    const searchModal = document.getElementById('school-search-modal');
    const overlay = searchModal ? searchModal.querySelector('.category-modal-overlay') : null;
    const closeButton = searchModal ? searchModal.querySelector('.category-modal-close') : null;
    const cancelButton = searchModal ? searchModal.querySelector('.school-search-cancel') : null;
    const searchForm = document.getElementById('school-search-form');
    const resultBody = document.getElementById('school-search-results');
    const paginationContainer = document.getElementById('school-search-pagination');

    const schoolNameInput = document.getElementById('school_name');
    const schoolIdInput = document.getElementById('school_id');
    const memberCityInput = document.getElementById('city');
    const memberDistrictInput = document.getElementById('city2');

    const citySelect = document.getElementById('search_city');
    const districtSelect = document.getElementById('search_district');
    const levelSelect = document.getElementById('search_school_level');
    const directInputField = document.getElementById('school_direct_input');
    const directConfirmButton = document.getElementById('school-direct-confirm');

    const defaultSelection = {
        id: schoolIdInput ? schoolIdInput.value : null,
        name: schoolNameInput ? schoolNameInput.value : null,
        city: memberCityInput ? memberCityInput.value : null,
        district: memberDistrictInput ? memberDistrictInput.value : null,
    };

    let selectedSchool = { ...defaultSelection };
    let currentPage = 1;

    if (citySelect && defaultSelection.city) {
        citySelect.dataset.defaultValue = defaultSelection.city;
    }
    if (districtSelect && defaultSelection.district) {
        districtSelect.dataset.defaultValue = defaultSelection.district;
    }

    if (!searchButton || !searchModal || !searchForm || !resultBody) {
        return;
    }

    function openModal() {
        const modalContent = searchModal.querySelector('.category-modal-content');
        if (modalContent) {
            modalContent.scrollTop = 0;
        }

        if (closeButton && !closeButton.querySelector('i')) {
            const icon = document.createElement('i');
            icon.className = 'fas fa-times';
            closeButton.appendChild(icon);
        }

        if (directInputField) {
            directInputField.value = '';
        }

        searchModal.style.display = 'flex';
        document.body.classList.add('modal-open');
        updateDirectInputState();
        loadSchools(1);
    }

    function closeModal() {
        searchModal.style.display = 'none';
        document.body.classList.remove('modal-open');
    }

    function populateSelect(selectElement, items, placeholder) {
        if (!selectElement) {
            return;
        }

        const currentValue = selectElement.value;
        selectElement.innerHTML = '';

        const defaultOption = document.createElement('option');
        defaultOption.value = '';
        defaultOption.textContent = placeholder;
        selectElement.appendChild(defaultOption);

        items.forEach((item) => {
            if (!item) {
                return;
            }
            const value = typeof item === 'object' ? item.value : item;
            const label = typeof item === 'object' ? (item.label ?? item.value) : item;

            if (!value) {
                return;
            }

            const option = document.createElement('option');
            option.value = value;
            option.textContent = label;
            selectElement.appendChild(option);
        });

        const defaultValue = selectElement.dataset.defaultValue || '';

        if (currentValue && selectElement.querySelector(`option[value="${currentValue}"]`)) {
            selectElement.value = currentValue;
        } else if (defaultValue && selectElement.querySelector(`option[value="${defaultValue}"]`)) {
            selectElement.value = defaultValue;
        }
        selectElement.dataset.defaultValue = '';
    }

    function loadSchools(page = 1) {
        const dataUrl = searchForm.dataset.url;
        if (!dataUrl) {
            return;
        }

        currentPage = page;
        resultBody.innerHTML = '<tr><td colspan="4" class="text-center">학교 정보를 불러오는 중입니다...</td></tr>';

        const formData = new FormData(searchForm);
        const params = new URLSearchParams(formData);
        params.set('page', page);

        fetch(`${dataUrl}?${params.toString()}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
            .then((response) => response.json())
            .then((data) => {
                const schools = data.data || [];
                renderSchoolRows(schools, data.pagination);
                renderSchoolPagination(data.pagination);
                if (data.filters) {
                    populateSelect(citySelect, data.filters.cities || [], '시/도 선택');
                    populateSelect(districtSelect, data.filters.districts || [], '시/군/구 선택');
                    populateSelect(levelSelect, data.filters.levels || [], '학교급 선택');
                }
            })
            .catch(() => {
                resultBody.innerHTML = '<tr><td colspan="4" class="text-center text-danger">학교 정보를 불러오는 중 오류가 발생했습니다.</td></tr>';
            });
    }

    function renderSchoolRows(schools, pagination) {
        if (!schools.length) {
            resultBody.innerHTML = '<tr><td colspan="4" class="text-center">검색 결과가 없습니다.</td></tr>';
            return;
        }

        const total = pagination ? pagination.total : schools.length;
        const perPage = pagination ? pagination.per_page : schools.length;
        const page = pagination ? pagination.current_page : 1;
        const baseNumber = pagination ? total - ((page - 1) * perPage) : schools.length;

        let template = '';
        schools.forEach((school, index) => {
            const schoolId = school.id ?? '';
            const schoolName = school.name ?? '';
            const schoolCity = school.city ?? '';
            const schoolDistrict = school.district ?? '';
            const region = [schoolCity, schoolDistrict].filter(Boolean).join(' ') || '-';
            const rowNumber = Math.max(1, baseNumber - index);
            const isSelected = selectedSchool && String(selectedSchool.id) === String(schoolId);

            template += `
                <tr class="school-row${isSelected ? ' selected' : ''}">
                    <td>${rowNumber}</td>
                    <td>${schoolName}</td>
                    <td>${region}</td>
                    <td class="school-select-action">
                        <button type="button"
                                class="btn btn-primary school-select-btn"
                                data-id="${schoolId}"
                                data-name="${schoolName}"
                                data-city="${schoolCity}"
                                data-district="${schoolDistrict}">
                            선택
                        </button>
                    </td>
                </tr>
            `;
        });

        resultBody.innerHTML = template;

        const buttons = resultBody.querySelectorAll('.school-select-btn');
        buttons.forEach((button) => {
            button.addEventListener('click', function () {
                const { id, name, city, district } = this.dataset;
                applySelectedSchool({ id, name, city, district });
            });
        });
    }

    function renderSchoolPagination(pagination) {
        if (!paginationContainer) {
            return;
        }

        const lastPage = Math.max(1, pagination?.last_page ?? 1);
        const current = Math.min(lastPage, pagination?.current_page ?? currentPage);
        const startPage = Math.max(1, current - 2);
        const endPage = Math.min(lastPage, current + 2);

        let html = '<nav aria-label="페이지 네비게이션"><ul class="pagination">';

        const pageItem = (page, label, disabled = false) => {
            if (disabled) {
                return `<li class="page-item disabled"><span class="page-link">${label}</span></li>`;
            }
            return `<li class="page-item"><a href="#" class="page-link" data-page="${page}">${label}</a></li>`;
        };

        html += pageItem(1, '<i class="fas fa-angle-double-left"></i>', current === 1);
        html += pageItem(current - 1, '<i class="fas fa-chevron-left"></i>', current === 1);

        for (let page = startPage; page <= endPage; page++) {
            if (page === current) {
                html += `<li class="page-item active"><span class="page-link">${page}</span></li>`;
            } else {
                html += pageItem(page, page);
            }
        }

        html += pageItem(current + 1, '<i class="fas fa-chevron-right"></i>', current === lastPage);
        html += pageItem(lastPage, '<i class="fas fa-angle-double-right"></i>', current === lastPage);
        html += '</ul></nav>';

        paginationContainer.innerHTML = html;

        paginationContainer.querySelectorAll('a[data-page]').forEach((link) => {
            link.addEventListener('click', function (event) {
                event.preventDefault();
                const targetPage = Number(this.dataset.page);
                if (!targetPage || targetPage === currentPage) {
                    return;
                }
                loadSchools(targetPage);
            });
        });
    }

    function applySelectedSchool(data) {
        if (!data) {
            return;
        }

        selectedSchool = { ...data };

        if (schoolNameInput) {
            schoolNameInput.value = data.name || '';
        }
        if (schoolIdInput) {
            schoolIdInput.value = data.id || '';
        }
        if (memberCityInput) {
            memberCityInput.value = data.city || '';
        }
        if (memberDistrictInput) {
            memberDistrictInput.value = data.district || '';
        }
        if (citySelect) {
            citySelect.dataset.defaultValue = data.city || '';
        }
        if (districtSelect) {
            districtSelect.dataset.defaultValue = data.district || '';
        }

        closeModal();
    }

    function updateDirectInputState() {
        if (!directConfirmButton) {
            return;
        }

        const hasValue = directInputField && directInputField.value.trim().length > 0;
        directConfirmButton.disabled = !hasValue;

        if (hasValue) {
            focusInputWithoutScroll(directInputField, '#school-search-modal .category-modal-content');
        }
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

    searchButton.addEventListener('click', openModal);
    overlay?.addEventListener('click', closeModal);
    closeButton?.addEventListener('click', closeModal);
    cancelButton?.addEventListener('click', closeModal);

    searchForm.addEventListener('submit', function (event) {
        event.preventDefault();
        selectedSchool = null;
        loadSchools(1);
    });

    [citySelect, districtSelect, levelSelect].forEach((select) => {
        select?.addEventListener('change', function () {
            if (select === citySelect && districtSelect) {
                districtSelect.dataset.defaultValue = '';
                districtSelect.value = '';
            }
            selectedSchool = null;
            loadSchools(1);
        });
    });

    directInputField?.addEventListener('input', function () {
        selectedSchool = null;
        updateDirectInputState();
    });

    directConfirmButton?.addEventListener('click', function () {
        const directValue = directInputField ? directInputField.value.trim() : '';
        if (!directValue) {
            alert('학교명을 입력해 주세요.');
            focusInputWithoutScroll(directInputField, '#school-search-modal .category-modal-content');
            return;
        }

        if (schoolNameInput) {
            schoolNameInput.value = directValue;
        }
        if (schoolIdInput) {
            schoolIdInput.value = '';
        }
        if (memberCityInput) {
            memberCityInput.value = citySelect?.value || '';
        }
        if (memberDistrictInput) {
            memberDistrictInput.value = districtSelect?.value || '';
        }

        selectedSchool = null;
        closeModal();
    });

    updateDirectInputState();
});

/**
 * 백오피스 학교 검색 모달 제어 스크립트
 */

document.addEventListener('DOMContentLoaded', function () {
    const searchButton = document.getElementById('school-search-btn');
    const searchModal = document.getElementById('school-search-modal');
    const overlay = searchModal ? searchModal.querySelector('.category-modal-overlay') : null;
    const closeButton = searchModal ? searchModal.querySelector('.category-modal-close') : null;
    const cancelButton = searchModal ? searchModal.querySelector('.school-search-cancel') : null;
    const confirmButton = document.getElementById('school-select-confirm');
    const searchForm = document.getElementById('school-search-form');
    const resultBody = document.getElementById('school-search-results');

    const schoolNameInput = document.getElementById('school_name');
    const schoolIdInput = document.getElementById('school_id');
    const memberCityInput = document.getElementById('city');
    const memberDistrictInput = document.getElementById('city2');

    const citySelect = document.getElementById('search_city');
    const districtSelect = document.getElementById('search_district');
    const levelSelect = document.getElementById('search_school_level');

    const defaultSelection = {
        id: schoolIdInput ? schoolIdInput.value : null,
        name: schoolNameInput ? schoolNameInput.value : null,
        city: memberCityInput ? memberCityInput.value : null,
        district: memberDistrictInput ? memberDistrictInput.value : null,
    };
    let selectedSchool = { ...defaultSelection };

    if (citySelect && defaultSelection.city) {
        citySelect.dataset.defaultValue = defaultSelection.city;
    }
    if (districtSelect && defaultSelection.district) {
        districtSelect.dataset.defaultValue = defaultSelection.district;
    }

    if (!searchButton || !searchModal || !searchForm || !resultBody) {
        return;
    }

    /**
     * 모달 열기
     */
    function openModal() {
        searchModal.style.display = 'flex';
        document.body.classList.add('modal-open');
        toggleConfirmButton(Boolean(selectedSchool && selectedSchool.id));
        loadSchools(function () {
            highlightSelectedRow();
        });
    }

    /**
     * 모달 닫기
     */
    function closeModal() {
        searchModal.style.display = 'none';
        document.body.classList.remove('modal-open');
    }

    /**
     * 확인 버튼 상태 제어
     */
    function toggleConfirmButton(enabled) {
        if (!confirmButton) {
            return;
        }
        confirmButton.disabled = !enabled;
    }

    /**
     * 셀렉트 옵션 구성
     */
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

    /**
     * 학교 목록 불러오기
     */
    function loadSchools(onComplete) {
        const dataUrl = searchForm.dataset.url;
        if (!dataUrl) {
            return;
        }

        resultBody.innerHTML = '<tr><td colspan="3" class="text-center">학교 정보를 불러오는 중입니다...</td></tr>';
        toggleConfirmButton(false);

        const formData = new FormData(searchForm);
        const params = new URLSearchParams(formData);

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
                renderSchoolRows(schools);
                if (data.filters) {
                    populateSelect(citySelect, data.filters.cities || [], '시/도 선택');
                    populateSelect(districtSelect, data.filters.districts || [], '시/군/구 선택');
                    populateSelect(levelSelect, data.filters.levels || [], '학교급 선택');
                }
                if (typeof onComplete === 'function') {
                    onComplete();
                }
            })
            .catch(() => {
                resultBody.innerHTML = '<tr><td colspan="3" class="text-center text-danger">학교 정보를 불러오는 중 오류가 발생했습니다.</td></tr>';
                if (typeof onComplete === 'function') {
                    onComplete();
                }
            });
    }

    /**
     * 학교 목록 렌더링
     */
    function renderSchoolRows(schools) {
        if (!schools.length) {
            resultBody.innerHTML = '<tr><td colspan="3" class="text-center">검색 결과가 없습니다.</td></tr>';
            toggleConfirmButton(false);
            return;
        }

        let template = '';
        schools.forEach((school) => {
            const schoolId = school.id ?? '';
            const schoolName = school.name ?? '';
            const schoolCity = school.city ?? '-';
            const schoolDistrict = school.district ?? '';
            const checked = selectedSchool && String(selectedSchool.id) === String(schoolId) ? 'checked' : '';

            template += `
                <tr class="school-row" data-id="${schoolId}" data-name="${schoolName}" data-city="${schoolCity}" data-district="${schoolDistrict}">
                    <td class="school-select-cell">
                        <input type="checkbox" name="selected_school" value="${schoolId}" class="school-select-input" ${checked}>
                    </td>
                    <td>${schoolCity}</td>
                    <td>${schoolName}</td>
                </tr>
            `;
        });

        resultBody.innerHTML = template;

        const rows = resultBody.querySelectorAll('.school-row');
        const checkboxes = resultBody.querySelectorAll('input[name="selected_school"]');

        checkboxes.forEach((checkbox) => {
            checkbox.addEventListener('change', function () {
                if (!this.checked) {
                    return;
                }

                checkboxes.forEach((other) => {
                    if (other !== this) {
                        other.checked = false;
                    }
                });

                const row = this.closest('.school-row');
                if (!row) {
                    return;
                }

                selectedSchool = {
                    id: row.dataset.id,
                    name: row.dataset.name,
                    city: row.dataset.city,
                    district: row.dataset.district,
                };
                toggleConfirmButton(true);

                rows.forEach((item) => item.classList.remove('selected'));
                row.classList.add('selected');
            });
        });

        rows.forEach((row) => {
            row.addEventListener('click', function (event) {
                const checkbox = this.querySelector('input[name="selected_school"]');
                if (!checkbox) {
                    return;
                }

                if (event.target !== checkbox) {
                    checkbox.checked = true;
                    checkbox.dispatchEvent(new Event('change'));
                }
            });
        });
    }

    function highlightSelectedRow() {
        if (!selectedSchool || !selectedSchool.id) {
            return;
        }
        const checkbox = resultBody.querySelector(`.school-row[data-id="${selectedSchool.id}"] input[name="selected_school"]`);
        if (checkbox) {
            checkbox.checked = true;
            checkbox.dispatchEvent(new Event('change'));
        }
    }

    /**
     * 모달 오픈/클로즈 이벤트 바인딩
     */
    searchButton.addEventListener('click', openModal);
    overlay?.addEventListener('click', closeModal);
    closeButton?.addEventListener('click', closeModal);
    cancelButton?.addEventListener('click', closeModal);

    /**
     * 검색 폼 submit 이벤트
     */
    searchForm.addEventListener('submit', function (event) {
        event.preventDefault();
        selectedSchool = null;
        toggleConfirmButton(false);
        loadSchools();
    });

    /**
     * 필터 변경 시 재검색
     */
    [citySelect, districtSelect, levelSelect].forEach((select) => {
        select?.addEventListener('change', function () {
            if (select === citySelect && districtSelect) {
                districtSelect.dataset.defaultValue = '';
                districtSelect.value = '';
            }
            selectedSchool = null;
            toggleConfirmButton(false);
            loadSchools();
        });
    });

    /**
     * 확인 버튼 클릭 시 값 반영
     */
    confirmButton?.addEventListener('click', function () {
        if (!selectedSchool) {
            return;
        }

        if (schoolNameInput) {
            schoolNameInput.value = selectedSchool.name || '';
        }
        if (schoolIdInput) {
            schoolIdInput.value = selectedSchool.id || '';
        }
        if (memberCityInput) {
            memberCityInput.value = selectedSchool.city || '';
        }
        if (memberDistrictInput) {
            memberDistrictInput.value = selectedSchool.district || '';
        }

        if (citySelect) {
            citySelect.dataset.defaultValue = selectedSchool.city || '';
        }
        if (districtSelect) {
            districtSelect.dataset.defaultValue = selectedSchool.district || '';
        }

        closeModal();
    });
});
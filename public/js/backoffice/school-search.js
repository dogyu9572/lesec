/**
 * 백오피스 학교 검색 팝업 제어 스크립트 (부모창)
 */

document.addEventListener('DOMContentLoaded', function () {
    const searchButton = document.getElementById('school-search-btn');
    const schoolNameInput = document.getElementById('school_name');
    const schoolIdInput = document.getElementById('school_id');
    const memberCityInput = document.getElementById('city');
    const memberDistrictInput = document.getElementById('city2');

    if (!searchButton) {
        return;
    }

    function openModal() {
        // 팝업 URL
        const url = '/backoffice/popup-windows/school-search';
        const width = window.innerWidth <= 768 ? '100%' : '900';
        const height = window.innerHeight <= 768 ? '100%' : '700';
        window.open(url, 'schoolSearch', `width=${width},height=${height},left=100,top=100,scrollbars=yes,resizable=yes`);
    }

    // 부모창에서 호출되는 함수 (팝업에서 선택한 데이터를 받음)
    window.applySelectedSchool = function(data) {
        if (!data) {
            return;
        }

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
    };

    searchButton.addEventListener('click', openModal);
});


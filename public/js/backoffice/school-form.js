/**
 * 학교 관리 폼 JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    const sourceTypeRadios = document.querySelectorAll('input[name="source_type"]');

    // 구분 선택에 따른 필드 표시/숨김
    function toggleFieldsBySourceType() {
        const selectedSourceType = document.querySelector('input[name="source_type"]:checked')?.value;

        // API 선택 시 일부 필드는 읽기 전용 또는 비활성화
        if (selectedSourceType === 'api') {
            // API로 등록된 경우 수정 제한
            const editableFields = document.querySelectorAll('#city, #district, #school_level, #school_name');
            editableFields.forEach(field => {
                if (field) {
                    field.disabled = false;
                }
            });
        } else {
            // 사용자 등록 또는 관리자 등록 시 모든 필드 활성화
            const allFields = document.querySelectorAll('#city, #district, #school_level, #school_name');
            allFields.forEach(field => {
                if (field) {
                    field.disabled = false;
                }
            });
        }
    }

    // 구분 변경 시 이벤트 리스너
    sourceTypeRadios.forEach(radio => {
        radio.addEventListener('change', toggleFieldsBySourceType);
    });

    // 초기 실행
    toggleFieldsBySourceType();
});


document.addEventListener('DOMContentLoaded', function() {
    // 전체 선택/해제
    var selectAllCheckbox = document.getElementById('select-all-checkbox');
    var applicationCheckboxes = document.querySelectorAll('.application-checkbox');
    var printEstimateBtn = document.getElementById('print-estimate-btn');

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            applicationCheckboxes.forEach(function(checkbox) {
                checkbox.checked = selectAllCheckbox.checked;
            });
        });
    }

    // 개별 체크박스 변경 시 전체 선택 체크박스 상태 업데이트
    applicationCheckboxes.forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            var totalCheckboxes = applicationCheckboxes.length;
            var checkedCheckboxes = document.querySelectorAll('.application-checkbox:checked').length;
            if (selectAllCheckbox) {
                selectAllCheckbox.checked = totalCheckboxes === checkedCheckboxes;
            }
        });
    });

    // 견적서 출력 버튼 클릭
    if (printEstimateBtn) {
        printEstimateBtn.addEventListener('click', function() {
            var selectedCheckboxes = document.querySelectorAll('.application-checkbox:checked');
            var selectedIds = Array.from(selectedCheckboxes).map(function(checkbox) {
                return checkbox.value;
            });

            if (selectedIds.length === 0) {
                alert('견적서를 출력할 항목을 선택해주세요.');
                return;
            }

            // 선택된 ID들을 쿼리 파라미터로 전달하여 하나의 페이지에서 순차적으로 출력
            var url = '/print/estimate?ids=' + selectedIds.join(',');
            window.open(url, '_blank');
        });
    }
});


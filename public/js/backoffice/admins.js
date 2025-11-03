/**
 * 관리자 관리 페이지 JavaScript
 */

// 페이지 로드 시 초기화
document.addEventListener('DOMContentLoaded', function() {
    checkSessionMessage();
    initBulkActions();
});

// 세션 메시지 확인 함수
function checkSessionMessage() {
    const successMessage = document.querySelector('.alert-success');
    if (successMessage && successMessage.textContent.trim()) {
        // 통합 모달 시스템 사용
        if (window.AppUtils && AppUtils.modal) {
            AppUtils.modal.success(successMessage.textContent.trim());
        }
        successMessage.style.display = 'none';
    }
}

// 일괄 작업 초기화
function initBulkActions() {
    const selectAllCheckbox = document.getElementById('select-all');
    const adminCheckboxes = document.querySelectorAll('.admin-checkbox');
    const bulkDeleteBtn = document.getElementById('bulk-delete-btn');

    if (!selectAllCheckbox || !bulkDeleteBtn) return;

    // 전체 선택/해제
    selectAllCheckbox.addEventListener('change', function() {
        adminCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateBulkDeleteButton();
    });

    // 개별 체크박스 변경 시
    adminCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateSelectAllCheckbox();
            updateBulkDeleteButton();
        });
    });

    // 일괄 삭제 버튼 클릭
    bulkDeleteBtn.addEventListener('click', function() {
        const selectedAdmins = Array.from(adminCheckboxes)
            .filter(checkbox => checkbox.checked)
            .map(checkbox => checkbox.value);

        if (selectedAdmins.length === 0) {
            alert('삭제할 관리자를 선택해주세요.');
            return;
        }

        if (confirm(`선택한 ${selectedAdmins.length}명의 관리자를 삭제하시겠습니까?`)) {
            bulkDeleteAdmins(selectedAdmins);
        }
    });

    // 초기 버튼 상태 설정 (disabled 상태에서도 색상 유지)
    updateBulkDeleteButton();
    
    // 초기 로드 시 버튼 disabled 상태 확인
    if (adminCheckboxes.length === 0 || Array.from(adminCheckboxes).filter(cb => cb.checked).length === 0) {
        bulkDeleteBtn.disabled = true;
    }
}

// 전체 선택 체크박스 상태 업데이트
function updateSelectAllCheckbox() {
    const selectAllCheckbox = document.getElementById('select-all');
    const adminCheckboxes = document.querySelectorAll('.admin-checkbox');
    const checkedCount = Array.from(adminCheckboxes).filter(checkbox => checkbox.checked).length;
    const totalCount = adminCheckboxes.length;

    if (selectAllCheckbox && totalCount > 0) {
        selectAllCheckbox.checked = checkedCount === totalCount;
    }
}

// 일괄 삭제 버튼 상태 업데이트
function updateBulkDeleteButton() {
    const bulkDeleteBtn = document.getElementById('bulk-delete-btn');
    const checkedCount = Array.from(document.querySelectorAll('.admin-checkbox'))
        .filter(checkbox => checkbox.checked).length;

    if (bulkDeleteBtn) {
        bulkDeleteBtn.disabled = checkedCount === 0;
        if (checkedCount > 0) {
            bulkDeleteBtn.innerHTML = `<i class="fas fa-trash"></i> 선택 삭제 (${checkedCount})`;
        } else {
            bulkDeleteBtn.innerHTML = `<i class="fas fa-trash"></i> 선택 삭제`;
        }
    }
}

// 일괄 삭제 실행
function bulkDeleteAdmins(adminIds) {
    const formData = new FormData();
    adminIds.forEach(id => formData.append('admin_ids[]', id));

    // CSRF 토큰 가져오기
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    fetch('/backoffice/admins/bulk-destroy', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('선택한 관리자가 삭제되었습니다.');
            location.reload();
        } else {
            alert('삭제 중 오류가 발생했습니다: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('삭제 중 오류가 발생했습니다.');
    });
}


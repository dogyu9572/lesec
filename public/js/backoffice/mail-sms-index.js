/**
 * 메일/SMS 관리 페이지 JavaScript
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
    const messageCheckboxes = document.querySelectorAll('.message-checkbox');
    const bulkDeleteBtnHeader = document.getElementById('bulk-delete-btn-header');

    if (!selectAllCheckbox) return;

    // 전체 선택/해제
    selectAllCheckbox.addEventListener('change', function() {
        messageCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateBulkDeleteButton();
    });

    // 개별 체크박스 변경 시
    messageCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateSelectAllCheckbox();
            updateBulkDeleteButton();
        });
    });

    // 일괄 삭제 버튼 클릭 (목록 옆 버튼)
    if (bulkDeleteBtnHeader) {
        bulkDeleteBtnHeader.addEventListener('click', function() {
            const selectedMessages = Array.from(messageCheckboxes)
                .filter(checkbox => checkbox.checked)
                .map(checkbox => checkbox.value);

            if (selectedMessages.length === 0) {
                alert('삭제할 발송 정보를 선택해주세요.');
                return;
            }

            if (confirm(`선택한 ${selectedMessages.length}개의 발송 정보를 삭제하시겠습니까?`)) {
                bulkDeleteMessages(selectedMessages);
            }
        });
    }

    // 초기 버튼 상태 설정
    updateBulkDeleteButton();
    
    // 초기 로드 시 버튼 disabled 상태 확인
    const checkedCount = Array.from(messageCheckboxes).filter(cb => cb.checked).length;
    if (bulkDeleteBtnHeader) {
        bulkDeleteBtnHeader.disabled = checkedCount === 0;
    }
}

// 전체 선택 체크박스 상태 업데이트
function updateSelectAllCheckbox() {
    const selectAllCheckbox = document.getElementById('select-all');
    const messageCheckboxes = document.querySelectorAll('.message-checkbox');
    const checkedCount = Array.from(messageCheckboxes).filter(checkbox => checkbox.checked).length;
    const totalCount = messageCheckboxes.length;

    if (selectAllCheckbox && totalCount > 0) {
        selectAllCheckbox.checked = checkedCount === totalCount;
    }
}

// 일괄 삭제 버튼 상태 업데이트
function updateBulkDeleteButton() {
    const bulkDeleteBtnHeader = document.getElementById('bulk-delete-btn-header');
    const checkedCount = Array.from(document.querySelectorAll('.message-checkbox'))
        .filter(checkbox => checkbox.checked).length;
    
    if (bulkDeleteBtnHeader) {
        bulkDeleteBtnHeader.disabled = checkedCount === 0;
    }
}

// 일괄 삭제 실행
function bulkDeleteMessages(messageIds) {
    const formData = new FormData();
    messageIds.forEach(id => formData.append('message_ids[]', id));

    // CSRF 토큰 가져오기
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    fetch('/backoffice/mail-sms/bulk-destroy', {
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
            alert(data.message || '선택한 발송 정보가 삭제되었습니다.');
            location.reload();
        } else {
            alert('삭제 중 오류가 발생했습니다: ' + (data.message || '알 수 없는 오류'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('삭제 중 오류가 발생했습니다.');
    });
}


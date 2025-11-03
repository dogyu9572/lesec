/**
 * 관리자 생성/수정 폼 JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    initLoginIdCheck();
});

/**
 * 아이디 중복 체크 초기화
 */
function initLoginIdCheck() {
    const loginIdInput = document.getElementById('login_id');
    const checkBtn = document.getElementById('check-login-id-btn');
    const messageDiv = document.getElementById('login-id-message');

    if (!loginIdInput || !checkBtn || !messageDiv) return;

    let isChecked = false;
    let lastCheckedValue = '';

    // 중복 체크 버튼 클릭
    checkBtn.addEventListener('click', function() {
        const loginId = loginIdInput.value.trim();

        if (!loginId) {
            showMessage('아이디를 입력해주세요.', 'error');
            return;
        }

        // 이전에 체크한 값과 같으면 재체크하지 않음
        if (isChecked && lastCheckedValue === loginId) {
            return;
        }

        checkLoginIdDuplicate(loginId);
    });

    // 아이디 입력 시 체크 상태 초기화
    loginIdInput.addEventListener('input', function() {
        if (isChecked && lastCheckedValue !== this.value.trim()) {
            isChecked = false;
            clearMessage();
        }
    });

    /**
     * 아이디 중복 체크
     */
    function checkLoginIdDuplicate(loginId) {
        checkBtn.disabled = true;
        checkBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 확인 중...';

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        fetch('/backoffice/admins/check-login-id', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ login_id: loginId })
        })
        .then(response => response.json())
        .then(data => {
            checkBtn.disabled = false;
            checkBtn.innerHTML = '<i class="fas fa-check"></i> 중복 확인';

            if (data.available) {
                isChecked = true;
                lastCheckedValue = loginId;
                showMessage(data.message, 'success');
            } else {
                isChecked = false;
                showMessage(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            checkBtn.disabled = false;
            checkBtn.innerHTML = '<i class="fas fa-check"></i> 중복 확인';
            showMessage('중복 체크 중 오류가 발생했습니다.', 'error');
        });
    }

    /**
     * 메시지 표시
     */
    function showMessage(message, type) {
        messageDiv.textContent = message;
        messageDiv.className = 'login-id-message ' + type;

        if (type === 'success') {
            messageDiv.style.color = '#28a745';
        } else {
            messageDiv.style.color = '#dc3545';
        }
    }

    /**
     * 메시지 제거
     */
    function clearMessage() {
        messageDiv.textContent = '';
        messageDiv.className = 'login-id-message';
        messageDiv.style.color = '';
    }

    // 폼 제출 시 체크 여부 확인
    const adminForm = document.getElementById('adminForm');
    if (adminForm) {
        adminForm.addEventListener('submit', function(e) {
            const loginId = loginIdInput.value.trim();
            
            if (loginId && (!isChecked || lastCheckedValue !== loginId)) {
                if (!confirm('아이디 중복 확인을 하지 않았습니다. 계속 진행하시겠습니까?')) {
                    e.preventDefault();
                    return false;
                }
            }
        });
    }
}


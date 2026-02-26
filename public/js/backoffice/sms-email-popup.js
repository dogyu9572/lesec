/**
 * 백오피스 SMS/메일 발송 팝업 제어 스크립트
 */

document.addEventListener('DOMContentLoaded', function () {
    const smsEmailContent = document.getElementById('sms-email-content');
    const smsEmailCharCount = document.getElementById('sms-email-char-count');
    const smsEmailMaxChars = document.getElementById('sms-email-max-chars');
    const smsEmailSendBtn = document.getElementById('sms-email-send-btn');
    const reservationIdsInput = document.getElementById('reservation-ids');
    const reservationIdInput = document.getElementById('reservation-id');
    const applicationTypeInput = document.getElementById('application-type');
    const applicationIdsInput = document.getElementById('application-ids');
    const participantIdsInput = document.getElementById('participant-ids');
    
    // 글자 수 업데이트
    function updateCharCount() {
        if (smsEmailContent && smsEmailCharCount) {
            const currentLength = smsEmailContent.value.length;
            const maxLength = parseInt(smsEmailMaxChars.textContent) || 2000;
            smsEmailCharCount.textContent = currentLength;
            
            // 글자 수가 최대치에 가까우면 경고 색상
            if (currentLength > maxLength * 0.9) {
                smsEmailCharCount.style.color = '#dc3545';
            } else {
                smsEmailCharCount.style.color = '';
            }
        }
    }
    
    // 글자 수 카운터
    if (smsEmailContent) {
        smsEmailContent.addEventListener('input', updateCharCount);
        updateCharCount();
    }
    
    // 발송 버튼
    if (smsEmailSendBtn) {
        smsEmailSendBtn.addEventListener('click', function() {
            const messageType = document.querySelector('input[name="message_type"]:checked')?.value;
            const content = smsEmailContent?.value.trim();
            
            if (!content) {
                alert('메시지 내용을 입력해주세요.');
                return;
            }
            
            // reservation_id와 application_ids/participant_ids가 있는 경우 (명단 편집 페이지)
            const reservationId = reservationIdInput ? reservationIdInput.value : null;
            const applicationType = applicationTypeInput ? applicationTypeInput.value : null;
            const applicationIds = applicationIdsInput ? applicationIdsInput.value : null;
            const participantIds = participantIdsInput ? participantIdsInput.value : null;
            
            // reservation_ids가 있는 경우 (명단 목록 페이지)
            const reservationIds = reservationIdsInput ? reservationIdsInput.value.split(',').filter(id => id.trim()) : [];
            
            // 발송할 대상이 없는지 확인
            if (!reservationId && reservationIds.length === 0) {
                alert('발송할 명단이 없습니다.');
                return;
            }
            
            if (reservationId && !applicationIds && !participantIds) {
                alert('발송할 명단이 없습니다.');
                return;
            }
            
            // SMS/EMAIL 발송 확인
            const confirmMessage = messageType === 'sms' 
                ? '선택한 명단에 SMS를 발송하시겠습니까?'
                : '선택한 명단에 이메일을 발송하시겠습니까?';
            
            if (!confirm(confirmMessage)) {
                return;
            }
            
            // 버튼 비활성화
            smsEmailSendBtn.disabled = true;
            smsEmailSendBtn.textContent = '발송 중...';
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            
            // 요청 데이터 구성
            const requestData = {
                message_type: messageType,
                content: content,
            };
            
            // 명단 편집 페이지인 경우 (선택된 신청자 ID 전달)
            if (reservationId) {
                requestData.reservation_id = parseInt(reservationId, 10);
                requestData.application_type = applicationType;
                if (applicationIds) {
                    const appIds = applicationIds.split(',').filter(id => id.trim()).map(id => parseInt(id, 10)).filter(id => !isNaN(id) && id > 0);
                    if (appIds.length > 0) {
                        requestData.application_ids = appIds;
                    }
                }
                if (participantIds) {
                    const partIds = participantIds.split(',').filter(id => id.trim()).map(id => parseInt(id, 10)).filter(id => !isNaN(id) && id > 0);
                    if (partIds.length > 0) {
                        requestData.participant_ids = partIds;
                    }
                }
            } else {
                // 명단 목록 페이지인 경우 (reservation_ids 전달)
                const reservationIdsInt = reservationIds
                    .map(id => {
                        const parsed = parseInt(id.trim(), 10);
                        return isNaN(parsed) ? null : parsed;
                    })
                    .filter(id => id !== null && id > 0);
                
                if (reservationIdsInt.length === 0) {
                    alert('유효한 명단 ID가 없습니다.');
                    smsEmailSendBtn.disabled = false;
                    smsEmailSendBtn.textContent = '발송';
                    return;
                }
                requestData.reservation_ids = reservationIdsInt;
            }
            
            fetch('/backoffice/rosters/send-sms', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || '',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                body: JSON.stringify(requestData),
            })
            .then(async response => {
                const contentType = response.headers.get('content-type') || '';
                if (!response.ok) {
                    let errorMessage = `HTTP error! status: ${response.status}`;
                    if (contentType.includes('application/json')) {
                        const errorData = await response.json();
                        errorMessage = errorData.message || errorMessage;
                    }
                    throw new Error(errorMessage);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    const successMessage = messageType === 'sms' ? 'SMS가 발송되었습니다.' : '이메일이 발송되었습니다.';
                    alert(data.message || successMessage);
                    // 부모 창에 결과 전달 (선택사항)
                    if (window.opener) {
                        window.opener.postMessage({
                            type: 'sms-sent',
                            success: true,
                            message: data.message,
                        }, '*');
                    }
                    window.close();
                } else {
                    alert(data.message || '발송 중 오류가 발생했습니다.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert(error.message || '발송 중 오류가 발생했습니다.');
            })
            .finally(() => {
                // 버튼 활성화
                smsEmailSendBtn.disabled = false;
                smsEmailSendBtn.textContent = '발송';
            });
        });
    }
});

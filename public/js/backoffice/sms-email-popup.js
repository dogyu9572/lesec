/**
 * 백오피스 SMS/메일 발송 팝업 제어 스크립트
 */

document.addEventListener('DOMContentLoaded', function () {
    const smsEmailContent = document.getElementById('sms-email-content');
    const smsEmailCharCount = document.getElementById('sms-email-char-count');
    const smsEmailMaxChars = document.getElementById('sms-email-max-chars');
    const smsEmailSendBtn = document.getElementById('sms-email-send-btn');
    const reservationIdsInput = document.getElementById('reservation-ids');
    
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
            const reservationIds = reservationIdsInput ? reservationIdsInput.value.split(',').filter(id => id.trim()) : [];
            
            if (reservationIds.length === 0) {
                alert('발송할 명단이 없습니다.');
                return;
            }
            
            const messageType = document.querySelector('input[name="message_type"]:checked')?.value;
            const content = smsEmailContent?.value.trim();
            
            if (!content) {
                alert('메시지 내용을 입력해주세요.');
                return;
            }
            
            if (messageType === 'email') {
                alert('메일 발송 기능은 준비 중입니다.');
                return;
            }
            
            // SMS 발송
            if (messageType === 'sms') {
                if (!confirm('선택한 명단에 SMS를 발송하시겠습니까?')) {
                    return;
                }
                
                // 버튼 비활성화
                smsEmailSendBtn.disabled = true;
                smsEmailSendBtn.textContent = '발송 중...';
                
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                
                fetch('/backoffice/rosters/send-sms', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken || '',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        reservation_ids: reservationIds,
                        message_type: messageType,
                        content: content,
                    }),
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        alert(data.message || 'SMS가 발송되었습니다.');
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
                        alert(data.message || 'SMS 발송 중 오류가 발생했습니다.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('SMS 발송 중 오류가 발생했습니다.');
                })
                .finally(() => {
                    // 버튼 활성화
                    smsEmailSendBtn.disabled = false;
                    smsEmailSendBtn.textContent = '발송';
                });
            }
        });
    }
});

document.addEventListener('DOMContentLoaded', function() {
    var config = document.getElementById('bulk-upload-config');
    if (!config) return;

    var bulkUploadBtn = document.getElementById('bulk-upload-btn');
    var bulkUploadFile = document.getElementById('bulk-upload-file');
    var uploadUrl = config.getAttribute('data-upload-url') || '';
    var csrfToken = config.getAttribute('data-csrf-token') || '';

    if (bulkUploadBtn && bulkUploadFile) {
        bulkUploadBtn.addEventListener('click', function() {
            bulkUploadFile.click();
        });

        bulkUploadFile.addEventListener('change', function(e) {
            var file = e.target.files && e.target.files[0];
            if (!file) return;

            var formData = new FormData();
            formData.append('file', file);

            // 업로드 중 버튼 비활성화
            bulkUploadBtn.disabled = true;
            bulkUploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 업로드 중...';

            fetch(uploadUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                body: formData
            })
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                if (data.success) {
                    var message = data.message || '업로드가 완료되었습니다.';
                    if (data.error_count > 0 && data.errors && data.errors.length > 0) {
                        var errorDetails = data.errors.slice(0, 5).join('\n');
                        if (data.errors.length > 5) {
                            errorDetails += '\n... 외 ' + (data.errors.length - 5) + '건';
                        }
                        alert(message + '\n\n오류 상세:\n' + errorDetails);
                    } else {
                        alert(message);
                    }
                    location.reload();
                } else {
                    alert(data.message || '업로드 중 오류가 발생했습니다.');
                }
            })
            .catch(function(error) {
                console.error('Upload error:', error);
                alert('업로드 중 오류가 발생했습니다.');
            })
            .finally(function() {
                // 버튼 복원
                bulkUploadBtn.disabled = false;
                bulkUploadBtn.innerHTML = '<i class="fas fa-upload"></i> 일괄 업로드';
                // 파일 input 초기화
                bulkUploadFile.value = '';
            });
        });
    }
});


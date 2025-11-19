document.addEventListener('DOMContentLoaded', function() {
    var config = document.getElementById('bulk-upload-config');
    if (!config) return;

    var bulkUploadBtn = document.getElementById('bulk-upload-btn');
    var uploadUrl = config.getAttribute('data-upload-url') || '';
    var searchUrl = config.getAttribute('data-search-url') || '';
    var csrfToken = config.getAttribute('data-csrf-token') || '';

    var selectedProgram = null;
    var currentProgramPage = 1;

    // 모달 닫기 함수
    function closeBulkUploadModal() {
        var modal = document.getElementById('bulk-upload-modal');
        if (modal) {
            modal.style.display = 'none';
            selectedProgram = null;
            var bulkUploadFile = document.getElementById('bulk-upload-file');
            if (bulkUploadFile) {
                bulkUploadFile.value = '';
            }
            updateBulkUploadSection();
        }
    }

    // 일괄 업로드 버튼 클릭 시 모달 열기
    if (bulkUploadBtn) {
        bulkUploadBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            var modal = document.getElementById('bulk-upload-modal');
            if (!modal) {
                console.error('모달을 찾을 수 없습니다.');
                return;
            }
            
            modal.style.display = 'flex';
            selectedProgram = null;
            currentProgramPage = 1;
            updateBulkUploadSection();
            searchPrograms(1);
        });
    }

    // 모달 닫기
    document.addEventListener('click', function(e) {
        if (e.target.matches('#bulk-upload-modal .category-modal-close') || 
            e.target.closest('#bulk-upload-modal .category-modal-close')) {
            closeBulkUploadModal();
        }
        
        if (e.target.matches('#bulk-upload-modal .category-modal-overlay')) {
            closeBulkUploadModal();
        }
    });

    // 프로그램 검색 버튼
    var searchBtn = document.getElementById('bulk-upload-search-btn');
    if (searchBtn) {
        searchBtn.addEventListener('click', function() {
            searchPrograms(1);
        });
    }

    // 프로그램 검색 엔터키
    var programKeyword = document.getElementById('bulk_upload_program_keyword');
    if (programKeyword) {
        programKeyword.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                searchPrograms(1);
            }
        });
    }

    // 파일 선택 버튼
    var fileSelectBtn = document.getElementById('bulk-upload-file-select-btn');
    var bulkUploadFile = document.getElementById('bulk-upload-file');
    
    if (fileSelectBtn && bulkUploadFile) {
        fileSelectBtn.addEventListener('click', function() {
            bulkUploadFile.click();
        });
    }

    // 파일 선택 후 업로드
    if (bulkUploadFile) {
        bulkUploadFile.addEventListener('change', function(e) {
            var file = e.target.files && e.target.files[0];
            if (!file) return;

            if (!selectedProgram) {
                alert('프로그램을 먼저 선택해주세요.');
                bulkUploadFile.value = '';
                return;
            }

            var formData = new FormData();
            formData.append('file', file);
            formData.append('program_reservation_id', selectedProgram.id);

            // 업로드 중 버튼 비활성화
            fileSelectBtn.disabled = true;
            fileSelectBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 업로드 중...';

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
                    closeBulkUploadModal();
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
                if (fileSelectBtn) {
                    fileSelectBtn.disabled = false;
                    fileSelectBtn.innerHTML = '파일 선택';
                }
                // 파일 input 초기화
                if (bulkUploadFile) {
                    bulkUploadFile.value = '';
                }
            });
        });
    }

    // 프로그램 검색
    function searchPrograms(page) {
        currentProgramPage = page;
        var form = document.getElementById('bulk-upload-search-form');
        if (!form) return;

        var formData = new FormData(form);
        var params = new URLSearchParams(formData);
        params.append('page', page);
        params.append('reception_type', 'naver_form'); // 네이버 폼 프로그램만 조회

        fetch(searchUrl + '?' + params.toString())
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                renderProgramList(data.programs || [], data.pagination || {});
                renderProgramPagination(data.pagination || {});
            })
            .catch(function(error) {
                console.error('프로그램 검색 오류:', error);
                alert('프로그램 검색 중 오류가 발생했습니다.');
            });
    }

    // 프로그램 목록 렌더링
    function renderProgramList(programs, pagination) {
        var tbody = document.getElementById('bulk-upload-program-list-body');
        if (!tbody) return;

        var educationTypeLabels = {
            'middle_semester': '중등학기',
            'middle_vacation': '중등방학',
            'high_semester': '고등학기',
            'high_vacation': '고등방학',
            'special': '특별프로그램'
        };

        if (!programs || programs.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center">검색 결과가 없습니다.</td></tr>';
            return;
        }

        var total = pagination.total || programs.length;
        var perPage = pagination.per_page || programs.length;
        var current = pagination.current_page || currentProgramPage;
        var baseNumber = total - ((current - 1) * perPage);

        tbody.innerHTML = programs.map(function(program, index) {
            var participationSchedule = program.participation_schedule || '-';
            var programData = encodeURIComponent(JSON.stringify(program));
            var rowNumber = Math.max(1, baseNumber - index);

            return '<tr>' +
                '<td>' + rowNumber + '</td>' +
                '<td>' + (educationTypeLabels[program.education_type] || program.education_type || '-') + '</td>' +
                '<td>' + participationSchedule + '</td>' +
                '<td>' + (program.program_name || '-') + '</td>' +
                '<td>' +
                '<button type="button" class="btn btn-primary btn-sm bulk-program-select-btn" data-program="' + programData + '">선택</button>' +
                '</td>' +
                '</tr>';
        }).join('');

        // 선택 버튼 이벤트
        tbody.querySelectorAll('.bulk-program-select-btn').forEach(function(button) {
            button.addEventListener('click', function() {
                selectedProgram = JSON.parse(decodeURIComponent(this.dataset.program));
                updateBulkUploadSection();
            });
        });
    }

    // 프로그램 페이지네이션 렌더링
    function renderProgramPagination(pagination) {
        var container = document.getElementById('bulk-upload-pagination');
        if (!container) return;

        var lastPage = Math.max(1, pagination.last_page || 1);
        var current = Math.min(lastPage, pagination.current_page || currentProgramPage);
        var startPage = Math.max(1, current - 2);
        var endPage = Math.min(lastPage, current + 2);

        var html = '<nav aria-label="페이지 네비게이션"><ul class="pagination">';

        var pageItem = function(page, label, disabled) {
            if (disabled) {
                return '<li class="page-item disabled"><span class="page-link">' + label + '</span></li>';
            }
            return '<li class="page-item"><a href="#" class="page-link" data-bulk-program-page="' + page + '">' + label + '</a></li>';
        };

        html += pageItem(1, '<i class="fas fa-angle-double-left"></i>', current === 1);
        html += pageItem(current - 1, '<i class="fas fa-chevron-left"></i>', current === 1);

        for (var i = startPage; i <= endPage; i++) {
            if (i === current) {
                html += '<li class="page-item active"><span class="page-link">' + i + '</span></li>';
            } else {
                html += pageItem(i, i, false);
            }
        }

        html += pageItem(current + 1, '<i class="fas fa-chevron-right"></i>', current === lastPage);
        html += pageItem(lastPage, '<i class="fas fa-angle-double-right"></i>', current === lastPage);

        html += '</ul></nav>';
        container.innerHTML = html;

        container.querySelectorAll('a[data-bulk-program-page]').forEach(function(link) {
            link.addEventListener('click', function(event) {
                event.preventDefault();
                var targetPage = Number(this.dataset.bulkProgramPage);
                if (!targetPage || targetPage === currentProgramPage) {
                    return;
                }
                selectedProgram = null;
                updateBulkUploadSection();
                searchPrograms(targetPage);
            });
        });
    }

    // 일괄 업로드 섹션 업데이트
    function updateBulkUploadSection() {
        var section = document.getElementById('bulk-upload-section');
        var programInfo = document.getElementById('selected-program-info');
        
        if (selectedProgram && section && programInfo) {
            section.style.display = 'block';
            programInfo.textContent = selectedProgram.program_name || '';
        } else if (section) {
            section.style.display = 'none';
        }
    }

    // 전역 함수로 노출 (모달 내부에서 호출 가능하도록)
    window.closeBulkUploadModal = closeBulkUploadModal;
});

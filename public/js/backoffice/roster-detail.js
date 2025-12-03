document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('select-all-checkbox');
    const rosterCheckboxes = document.querySelectorAll('.roster-checkbox');

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            rosterCheckboxes.forEach((checkbox) => {
                checkbox.checked = selectAllCheckbox.checked;
            });
        });
    }

    const lotteryBtn = document.getElementById('lottery-btn');
    const lotteryConfirmModal = document.getElementById('lottery-confirm-modal');
    const lotteryConfirmCancel = document.getElementById('lottery-confirm-cancel');
    const lotteryConfirmSubmit = document.getElementById('lottery-confirm-submit');

    // 추첨 확인 버튼 처리 함수
    function initLotteryConfirmButton() {
        const submitBtn = document.getElementById('lottery-confirm-submit');
        if (!submitBtn) {
            return;
        }

        // 기존 버튼을 클론해서 교체 (모든 이벤트 리스너 제거)
        const newBtn = submitBtn.cloneNode(true);
        submitBtn.parentNode.replaceChild(newBtn, submitBtn);

        // onclick 직접 할당
        newBtn.onclick = function(e) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();

            if (!window.rosterDetailConfig || !window.rosterDetailConfig.lotteryUrl) {
                alert('추첨 URL을 찾을 수 없습니다. 페이지를 새로고침해주세요.');
                const modal = document.getElementById('lottery-confirm-modal');
                if (modal) {
                    modal.style.display = 'none';
                }
                return false;
            }

            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (!csrfToken) {
                alert('CSRF 토큰을 찾을 수 없습니다. 페이지를 새로고침해주세요.');
                const modal = document.getElementById('lottery-confirm-modal');
                if (modal) {
                    modal.style.display = 'none';
                }
                return false;
            }

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = window.rosterDetailConfig.lotteryUrl;

            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken.getAttribute('content');
            form.appendChild(csrfInput);

            document.body.appendChild(form);
            form.submit();

            const modal = document.getElementById('lottery-confirm-modal');
            if (modal) {
                modal.style.display = 'none';
            }

            return false;
        };
    }

    // 추첨 버튼 클릭 시 모달 열기 및 확인 버튼 초기화
    if (lotteryBtn && !lotteryBtn.disabled) {
        lotteryBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            if (lotteryConfirmModal) {
                lotteryConfirmModal.style.display = 'flex';
                // 모달이 열릴 때 버튼 초기화
                setTimeout(initLotteryConfirmButton, 100);
            }
        });
    }

    // 취소 버튼
    if (lotteryConfirmCancel) {
        lotteryConfirmCancel.addEventListener('click', function(e) {
            e.stopPropagation();
            if (lotteryConfirmModal) {
                lotteryConfirmModal.style.display = 'none';
            }
        });
    }

    // 초기화 (지연 실행)
    if (lotteryConfirmSubmit) {
        setTimeout(initLotteryConfirmButton, 500);
    }

    // 모달 배경 클릭 시 닫기
    if (lotteryConfirmModal) {
        lotteryConfirmModal.addEventListener('click', function(e) {
            if (e.target === lotteryConfirmModal) {
                lotteryConfirmModal.style.display = 'none';
            }
        });
    }

    const drawResultFilter = document.getElementById('draw-result-filter');
    const searchBtn = document.getElementById('search-btn');

    if (drawResultFilter && searchBtn) {
        searchBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            const filterValue = drawResultFilter.value;
            const rows = document.querySelectorAll('tbody tr[data-draw-result]');

            rows.forEach(function(row) {
                const drawResult = row.getAttribute('data-draw-result');
                if (filterValue === '' || drawResult === filterValue) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }

    const smsEmailBtn = document.getElementById('sms-email-btn');
    if (smsEmailBtn) {
        smsEmailBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            alert('SMS/메일 발송 기능은 준비 중입니다.');
        });
    }

    // 다운로드 모달
    const downloadBtn = document.getElementById('download-btn');
    const downloadModal = document.getElementById('download-modal');
    const downloadModalCancel = document.getElementById('download-modal-cancel');
    const downloadModalSubmit = document.getElementById('download-modal-submit');
    const selectAllColumns = document.getElementById('select-all-columns');
    const deselectAllColumns = document.getElementById('deselect-all-columns');
    const columnCheckboxes = document.querySelectorAll('.column-checkbox');

    if (downloadBtn) {
        downloadBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            
            if (downloadModal) {
                downloadModal.style.display = 'flex';
            } else {
                console.error('다운로드 모달을 찾을 수 없습니다.');
                alert('다운로드 모달을 찾을 수 없습니다. 페이지를 새로고침해주세요.');
            }
        }, true); // capture phase에서 실행
    } else {
        console.error('다운로드 버튼을 찾을 수 없습니다.');
    }

    if (downloadModalCancel) {
        downloadModalCancel.addEventListener('click', function(e) {
            e.stopPropagation();
            if (downloadModal) {
                downloadModal.style.display = 'none';
            }
        });
    }

    if (downloadModal) {
        downloadModal.addEventListener('click', function(e) {
            if (e.target === downloadModal) {
                downloadModal.style.display = 'none';
            }
        });
    }

    if (selectAllColumns) {
        selectAllColumns.addEventListener('click', function(e) {
            e.stopPropagation();
            columnCheckboxes.forEach(function(checkbox) {
                checkbox.checked = true;
            });
        });
    }

    if (deselectAllColumns) {
        deselectAllColumns.addEventListener('click', function(e) {
            e.stopPropagation();
            columnCheckboxes.forEach(function(checkbox) {
                checkbox.checked = false;
            });
        });
    }

    if (downloadModalSubmit) {
        downloadModalSubmit.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();

            if (!window.rosterDetailConfig || !window.rosterDetailConfig.downloadUrl) {
                alert('다운로드 URL을 찾을 수 없습니다. 페이지를 새로고침해주세요.');
                return;
            }

            // 선택된 컬럼 수집
            const selectedColumns = Array.from(columnCheckboxes)
                .filter(function(checkbox) {
                    return checkbox.checked;
                })
                .map(function(checkbox) {
                    return checkbox.value;
                });

            if (selectedColumns.length === 0) {
                alert('최소 하나 이상의 항목을 선택해주세요.');
                return;
            }

            // 선택된 행 ID 수집
            const selectedIds = Array.from(document.querySelectorAll('.roster-checkbox:checked'))
                .map(function(checkbox) {
                    return checkbox.value;
                });

            // 폼 생성 및 제출
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = window.rosterDetailConfig.downloadUrl;

            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (csrfToken) {
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = csrfToken.getAttribute('content');
                form.appendChild(csrfInput);
            }

            // 선택된 컬럼 추가
            selectedColumns.forEach(function(column) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'columns[]';
                input.value = column;
                form.appendChild(input);
            });

            // 선택된 ID 추가 (빈 배열이면 전체 다운로드)
            selectedIds.forEach(function(id) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'selected_ids[]';
                input.value = id;
                form.appendChild(input);
            });

            document.body.appendChild(form);
            form.submit();

            if (downloadModal) {
                downloadModal.style.display = 'none';
            }
        });
    }

    const deleteBtn = document.getElementById('delete-btn');
    if (deleteBtn) {
        deleteBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            if (confirm('정말 삭제하시겠습니까?')) {
                alert('삭제 기능은 준비 중입니다.');
            }
        });
    }

    const saveBtn = document.getElementById('save-btn');
    if (saveBtn) {
        saveBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            alert('저장 기능은 준비 중입니다.');
        });
    }

});



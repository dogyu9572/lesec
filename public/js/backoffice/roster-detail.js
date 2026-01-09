document.addEventListener('DOMContentLoaded', function() {
    // 프로그램 정보 가져오기
    const programInfo = window.rosterDetailConfig || {};
    const isIndividual = programInfo.applicationType === 'individual';
    const isLottery = programInfo.receptionType === 'lottery';
    
    const selectAllCheckbox = document.getElementById('select-all-checkbox');

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const rosterCheckboxes = document.querySelectorAll('.roster-checkbox');
            rosterCheckboxes.forEach((checkbox) => {
                checkbox.checked = selectAllCheckbox.checked;
            });
            updateSelectAllCheckbox();
        });
    }

    // 개별 체크박스 변경 시 전체 선택 체크박스 업데이트
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('roster-checkbox')) {
            updateSelectAllCheckbox();
        }
    });

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
            saveNewMembers();
        });
    }

    // 회원 검색 팝업 열기
    const memberSearchBtn = document.getElementById('member-search-btn');
    if (memberSearchBtn) {
        memberSearchBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            openMemberSearchPopup();
        });
    }

    /**
     * 회원 검색 팝업 열기
     */
    function openMemberSearchPopup() {
        const url = '/backoffice/popup-windows/member-search?selection_mode=multiple';
        const width = window.innerWidth <= 768 ? '100%' : '1000';
        const height = window.innerHeight <= 768 ? '100%' : '700';
        window.open(url, 'memberSearch', `width=${width},height=${height},left=100,top=100,scrollbars=yes,resizable=yes`);
    }

    /**
     * 팝업에서 선택한 회원을 명단에 추가
     */
    window.applySelectedMember = function(selectedMember) {
        if (!selectedMember) {
            return;
        }

        // 배열로 전달된 경우 (여러 명 선택)
        const selectedMembers = Array.isArray(selectedMember) ? selectedMember : [selectedMember];
        
        if (selectedMembers.length === 0) {
            return;
        }

        const tbody = document.querySelector('.board-table tbody');
        if (!tbody) {
            alert('명단 테이블을 찾을 수 없습니다.');
            return;
        }

        // 기존 행 개수 확인 (번호 계산용)
        const existingRows = tbody.querySelectorAll('tr:not(.no-data-row)');
        let currentRowNumber = existingRows.length;

        // 테이블 헤더에서 실제 컬럼 수 확인
        const thead = document.querySelector('.board-table thead');
        const headerCells = thead ? thead.querySelectorAll('th') : [];
        const columnCount = headerCells.length;

        selectedMembers.forEach((member) => {
            // 이미 추가된 회원인지 확인
            const existingRow = tbody.querySelector(`tr[data-member-id="${member.id}"]`);
            if (existingRow) {
                return; // 이미 추가된 회원은 건너뛰기
            }

            currentRowNumber++;
            const row = document.createElement('tr');
            row.dataset.memberId = member.id;
            row.dataset.isNew = 'true'; // 새로 추가된 행 표시
            
            // 개인 신청과 단체 신청 구분
            if (isIndividual) {
                // 개인 신청 컬럼: 체크박스, No, 신청번호, 신청자명, 학교명, 학년, 반, 생년월일, 성별, 결제상태, [추첨결과], 신청일시, 관리
                // 추첨: 13개, 선착순: 12개
                if (isLottery) {
                    // 추첨: 13개 컬럼
                    row.innerHTML = `
                        <td><input type="checkbox" class="roster-checkbox" value="${member.id}"></td>
                        <td>${currentRowNumber}</td>
                        <td>-</td>
                        <td>${member.name || '-'}</td>
                        <td>${member.school_name || '-'}</td>
                        <td>${member.grade || '-'}</td>
                        <td>${member.class_number || '-'}</td>
                        <td>${member.birthday ? formatBirthday(member.birthday) : '-'}</td>
                        <td>${member.gender || '-'}</td>
                        <td>-</td>
                        <td>-</td>
                        <td>-</td>
                        <td class="roster-table-actions">
                            <div style="display: flex; gap: 4px; align-items: center;">
                                <button type="button" class="btn btn-primary btn-sm" disabled style="opacity: 0.5;">
                                    보기
                                </button>
                                <button type="button" class="btn btn-danger btn-sm remove-roster-member-btn" data-member-id="${member.id}" data-skip-button>
                                   삭제
                                </button>
                            </div>
                        </td>
                    `;
                } else {
                    // 선착순: 12개 컬럼
                    row.innerHTML = `
                        <td><input type="checkbox" class="roster-checkbox" value="${member.id}"></td>
                        <td>${currentRowNumber}</td>
                        <td>-</td>
                        <td>${member.name || '-'}</td>
                        <td>${member.school_name || '-'}</td>
                        <td>${member.grade || '-'}</td>
                        <td>${member.class_number || '-'}</td>
                        <td>${member.birthday ? formatBirthday(member.birthday) : '-'}</td>
                        <td>${member.gender || '-'}</td>
                        <td>-</td>
                        <td>-</td>
                        <td class="roster-table-actions">
                            <div style="display: flex; gap: 4px; align-items: center;">
                                <button type="button" class="btn btn-primary btn-sm" disabled style="opacity: 0.5;">
                                    보기
                                </button>
                                <button type="button" class="btn btn-danger btn-sm remove-roster-member-btn" data-member-id="${member.id}" data-skip-button>
                                    삭제
                                </button>
                            </div>
                        </td>
                    `;
                }
            } else {
                // 단체 신청: 8개 컬럼 (체크박스, No, 이름, 학교, 학년, 반, 생년월일, 관리)
                row.innerHTML = `
                    <td><input type="checkbox" class="roster-checkbox" value="${member.id}"></td>
                    <td>${currentRowNumber}</td>
                    <td>${member.name || '-'}</td>
                    <td>${member.school_name || '-'}</td>
                    <td>${member.grade || '-'}</td>
                    <td>${member.class_number || '-'}</td>
                    <td>${member.birthday ? formatBirthday(member.birthday) : '-'}</td>
                    <td class="roster-table-actions">
                        <button type="button" class="btn btn-danger btn-sm remove-roster-member-btn" data-member-id="${member.id}" data-skip-button>
                           삭제
                        </button>
                    </td>
                `;
            }

            tbody.appendChild(row);
        });

        // 삭제 버튼 이벤트 리스너 추가
        attachRemoveButtonListeners();

        // 전체 선택 체크박스 업데이트
        updateSelectAllCheckbox();
    };

    /**
     * 생년월일 포맷팅
     */
    function formatBirthday(birthday) {
        if (!birthday) return '-';
        // YYYY-MM-DD 형식을 YYYY.MM.DD로 변환
        return birthday.replace(/-/g, '.');
    }

    /**
     * 삭제 버튼 이벤트 리스너 추가
     */
    function attachRemoveButtonListeners() {
        const removeButtons = document.querySelectorAll('.remove-roster-member-btn');
        removeButtons.forEach(btn => {
            if (!btn.hasAttribute('data-listener-attached')) {
                btn.setAttribute('data-listener-attached', 'true');
                btn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    // 개인 신청은 application-id, 단체 신청은 participant-id 사용
                    const applicationId = this.getAttribute('data-application-id');
                    const participantId = this.getAttribute('data-participant-id');
                    const id = applicationId || participantId;
                    const type = applicationId ? 'individual' : 'group';
                    if (id) {
                        removeRosterMember(id, type);
                    } else {
                        console.error('삭제할 ID를 찾을 수 없습니다.');
                    }
                });
            }
        });
    }

    /**
     * 명단에서 회원 삭제
     */
    function removeRosterMember(id, type) {
        if (!confirm('정말 이 회원을 명단에서 삭제하시겠습니까?')) {
            return;
        }

        const reservationId = programInfo.reservationId;
        if (!reservationId) {
            alert('프로그램 정보를 찾을 수 없습니다.');
            return;
        }

        // 행 찾기: 개인 신청은 data-application-id, 단체 신청은 data-participant-id 사용
        const rowSelector = type === 'individual' 
            ? `tr[data-application-id="${id}"]`
            : `tr[data-participant-id="${id}"]`;
        const row = document.querySelector(rowSelector);
        
        if (!row) {
            alert('삭제할 행을 찾을 수 없습니다.');
            return;
        }

        const isNew = row.getAttribute('data-is-new') === 'true';

        if (isNew) {
            // 새로 추가된 회원은 프론트엔드에서만 제거
            row.remove();
            renumberRows();
            updateSelectAllCheckbox();
        } else {
            // 기존 신청은 서버에서 삭제
            const requestBody = type === 'individual' 
                ? { member_ids: [id] }  // 개인 신청은 application_id를 member_ids로 전달 (컨트롤러에서 처리)
                : { participant_ids: [id] }; // 단체 신청은 participant_id 사용

            const removeMembersUrl = programInfo.removeMembersUrl || `/backoffice/rosters/${reservationId}/remove-members`;
            fetch(removeMembersUrl, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                body: JSON.stringify(requestBody),
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    return response.json();
                } else {
                    // JSON이 아닌 경우 (리다이렉트 등) 페이지 새로고침
                    window.location.reload();
                    return null;
                }
            })
            .then(data => {
                if (data) {
                    if (data.success) {
                        alert(data.message || '삭제되었습니다.');
                        window.location.reload();
                    } else {
                        alert(data.message || '삭제 중 오류가 발생했습니다.');
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('삭제 중 오류가 발생했습니다.');
            });
        }
    }

    /**
     * 새로 추가된 회원들을 저장
     */
    function saveNewMembers() {
        const reservationId = programInfo.reservationId;
        if (!reservationId) {
            alert('프로그램 정보를 찾을 수 없습니다.');
            return;
        }

        // 새로 추가된 회원들 수집
        const tbody = document.querySelector('.board-table tbody');
        if (!tbody) {
            alert('명단 테이블을 찾을 수 없습니다.');
            return;
        }

        const newRows = tbody.querySelectorAll('tr[data-is-new="true"]');
        if (newRows.length === 0) {
            alert('저장할 회원이 없습니다.');
            return;
        }

        const memberIds = [];
        newRows.forEach(row => {
            const memberId = row.getAttribute('data-member-id');
            if (memberId) {
                memberIds.push(parseInt(memberId));
            }
        });

        if (memberIds.length === 0) {
            alert('저장할 회원을 찾을 수 없습니다.');
            return;
        }

        if (!confirm(`총 ${memberIds.length}명의 회원을 명단에 추가하시겠습니까?`)) {
            return;
        }

        // 저장 버튼 비활성화
        if (saveBtn) {
            saveBtn.disabled = true;
            saveBtn.textContent = '저장 중...';
        }

        const storeMembersUrl = programInfo.storeMembersUrl || `/backoffice/rosters/${reservationId}/store-members`;
        fetch(storeMembersUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                member_ids: memberIds,
            }),
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                return response.json();
            } else {
                // JSON이 아닌 경우 (리다이렉트 등) 페이지 새로고침
                window.location.reload();
                return null;
            }
        })
        .then(data => {
            if (data) {
                if (data.success) {
                    alert(data.message || '저장되었습니다.');
                    window.location.reload();
                } else {
                    alert(data.message || '저장 중 오류가 발생했습니다.');
                    if (saveBtn) {
                        saveBtn.disabled = false;
                        saveBtn.textContent = '저장';
                    }
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('저장 중 오류가 발생했습니다.');
            if (saveBtn) {
                saveBtn.disabled = false;
                saveBtn.textContent = '저장';
            }
        });
    }

    /**
     * 행 번호 재정렬
     */
    function renumberRows() {
        const tbody = document.querySelector('.board-table tbody');
        if (!tbody) return;

        const rows = tbody.querySelectorAll('tr:not(.no-data-row)');
        rows.forEach((row, index) => {
            const numberCell = row.querySelector('td:nth-child(2)');
            if (numberCell) {
                numberCell.textContent = rows.length - index;
            }
        });
    }

    /**
     * 전체 선택 체크박스 업데이트
     */
    function updateSelectAllCheckbox() {
        if (!selectAllCheckbox) return;
        
        const allCheckboxes = document.querySelectorAll('.roster-checkbox');
        const checkedCheckboxes = document.querySelectorAll('.roster-checkbox:checked');
        
        if (allCheckboxes.length === 0) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
        } else if (checkedCheckboxes.length === allCheckboxes.length) {
            selectAllCheckbox.checked = true;
            selectAllCheckbox.indeterminate = false;
        } else if (checkedCheckboxes.length > 0) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = true;
        } else {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
        }
    }

    // 초기 삭제 버튼 이벤트 리스너 추가
    attachRemoveButtonListeners();

});



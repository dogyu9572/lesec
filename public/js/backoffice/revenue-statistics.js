document.addEventListener('DOMContentLoaded', function() {
    const addItemBtn = document.getElementById('add-item-btn');
    const itemsBody = document.getElementById('items-body');
    const itemsTable = document.getElementById('items-table');
    
    // itemIndex는 edit 페이지에서 전역 변수로 설정됨, create 페이지에서는 0부터 시작
    if (typeof window.itemIndex === 'undefined') {
        window.itemIndex = 0;
    }

    // 항목 추가 버튼 클릭
    if (addItemBtn) {
        addItemBtn.addEventListener('click', function() {
            addItemRow();
        });
    }

    // 테이블 내부 이벤트 위임
    if (itemsBody) {
        itemsBody.addEventListener('click', function(e) {
            // 삭제 버튼
            if (e.target.closest('.remove-item-btn')) {
                const row = e.target.closest('tr');
                if (row) {
                    removeItemRow(row);
                }
            }

            // 수정 버튼 (현재는 추가/수정이 동일하게 작동하므로 필요시 확장 가능)
            if (e.target.closest('.edit-item-btn')) {
                // 수정 로직은 필요시 구현
                console.log('수정 버튼 클릭');
            }
        });

        // 입력 필드 변경 감지 (필요시)
        itemsBody.addEventListener('change', function(e) {
            if (e.target.tagName === 'INPUT') {
                // 입력 값 변경 처리
                updateEmptyRowVisibility();
            }
        });
    }

    // 항목 행 추가
    function addItemRow() {
        const emptyRow = itemsBody.querySelector('.empty-row');
        if (emptyRow) {
            emptyRow.style.display = 'none';
        }

        const currentIndex = window.itemIndex;
        const newRow = document.createElement('tr');
        newRow.setAttribute('data-item-index', currentIndex);
        newRow.innerHTML = `
            <td>
                <input type="text" name="items[${currentIndex}][item_name]" class="form-control" placeholder="항목명을 입력하세요" required>
            </td>
            <td>
                <input type="number" name="items[${currentIndex}][participants_count]" class="form-control" placeholder="0" min="0" value="0">
            </td>
            <td>
                <input type="text" name="items[${currentIndex}][school_name]" class="form-control" placeholder="참가학교를 입력하세요">
            </td>
            <td>
                <input type="number" name="items[${currentIndex}][revenue]" class="form-control" placeholder="0" min="0" value="0">
            </td>
            <td>
                <div class="board-btn-group">
                    <button type="button" class="btn btn-primary btn-sm edit-item-btn">
                        <i class="fas fa-edit"></i> 수정
                    </button>
                    <button type="button" class="btn btn-danger btn-sm remove-item-btn">
                        <i class="fas fa-trash"></i> 삭제
                    </button>
                </div>
            </td>
        `;

        itemsBody.appendChild(newRow);
        window.itemIndex++;
        updateEmptyRowVisibility();
    }

    // 항목 행 삭제
    function removeItemRow(row) {
        if (confirm('이 항목을 삭제하시겠습니까?')) {
            row.remove();
            updateEmptyRowVisibility();
            
            // 인덱스 재정렬 (선택사항, 필요시 구현)
            reindexRows();
        }
    }

    // 빈 행 표시 여부 업데이트
    function updateEmptyRowVisibility() {
        const rows = itemsBody.querySelectorAll('tr:not(.empty-row)');
        const emptyRow = itemsBody.querySelector('.empty-row');
        
        if (rows.length === 0 && emptyRow) {
            emptyRow.style.display = '';
        } else if (emptyRow) {
            emptyRow.style.display = 'none';
        }
    }

    // 행 인덱스 재정렬 (선택사항)
    function reindexRows() {
        const rows = itemsBody.querySelectorAll('tr:not(.empty-row)');
        rows.forEach((row, index) => {
            row.setAttribute('data-item-index', index);
            
            // input name 속성 재설정
            const inputs = row.querySelectorAll('input');
            inputs.forEach(input => {
                const name = input.name;
                if (name) {
                    const match = name.match(/items\[(\d+)\]\[(.+)\]/);
                    if (match) {
                        input.name = `items[${index}][${match[2]}]`;
                    }
                }
            });
        });
        
        window.itemIndex = rows.length;
    }

    // 폼 제출 전 유효성 검사
    const form = document.getElementById('revenueStatisticsForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            const titleInput = document.getElementById('title');
            if (!titleInput || !titleInput.value.trim()) {
                e.preventDefault();
                alert('제목을 입력해주세요.');
                titleInput.focus();
                return false;
            }

            // 항목 유효성 검사
            const itemRows = itemsBody.querySelectorAll('tr:not(.empty-row)');
            let hasError = false;
            
            itemRows.forEach((row, index) => {
                const itemNameInput = row.querySelector('input[name*="[item_name]"]');
                if (itemNameInput && !itemNameInput.value.trim()) {
                    hasError = true;
                    alert(`${index + 1}번째 항목의 항목명을 입력해주세요.`);
                    if (itemNameInput) {
                        itemNameInput.focus();
                    }
                    return false;
                }
            });

            if (hasError) {
                e.preventDefault();
                return false;
            }
        });
    }

    // 다운로드 버튼 처리
    const downloadBtn = document.getElementById('download-btn');
    if (downloadBtn && downloadBtn.tagName === 'A') {
        downloadBtn.addEventListener('click', function(e) {
            // 다운로드는 기본 동작 사용
            // 필요시 AJAX로 처리할 수 있음
        });
    } else if (downloadBtn) {
        // create 페이지의 버튼은 비활성화 상태
        downloadBtn.disabled = true;
    }

    // 초기 빈 행 표시 여부 설정
    updateEmptyRowVisibility();
});


document.addEventListener('DOMContentLoaded', function() {
    const addItemBtn = document.getElementById('add-item-btn');
    const itemsBody = document.getElementById('items-body');
    const itemsTable = document.getElementById('items-table');

    if (typeof window.itemIndex === 'undefined') {
        window.itemIndex = 0;
    }

    function toggleSchoolTypeCustomInput(selectEl) {
        const row = selectEl.closest('tr');
        if (!row) {
            return;
        }
        const input = row.querySelector('.school-type-custom-input');
        if (!input) {
            return;
        }
        if (selectEl.value === 'custom') {
            input.style.display = '';
            input.setAttribute('required', 'required');
        } else {
            input.style.display = 'none';
            input.removeAttribute('required');
            input.value = '';
        }
    }

    function schoolTypeLabel(selectEl, customInputEl) {
        if (!selectEl) {
            return '';
        }
        if (selectEl.value === 'middle') {
            return '중등';
        }
        if (selectEl.value === 'high') {
            return '고등';
        }
        if (selectEl.value === 'custom') {
            return customInputEl && customInputEl.value ? customInputEl.value : '직접입력';
        }
        return '';
    }

    function setRowEditingState(row, isEditing) {
        if (!row) {
            return;
        }

        row.setAttribute('data-row-editing', isEditing ? '1' : '0');

        const schoolReadonly = row.querySelector('.school-type-readonly');
        const editor = row.querySelector('.school-type-editor');
        const schoolSelect = row.querySelector('.school-type-select');
        const customInput = row.querySelector('.school-type-custom-input');
        const participantsReadonly = row.querySelector('.participants-readonly');
        const participantsInput = row.querySelector('.participants-count');
        const revenueReadonly = row.querySelector('.revenue-readonly');
        const revenueInput = row.querySelector('.revenue-input');

        if (isEditing) {
            if (schoolReadonly) {
                schoolReadonly.style.display = 'none';
            }
            if (editor) {
                editor.style.display = '';
            }
            if (participantsReadonly) {
                participantsReadonly.style.display = 'none';
            }
            if (participantsInput) {
                participantsInput.style.display = '';
            }
            if (revenueReadonly) {
                revenueReadonly.style.display = 'none';
            }
            if (revenueInput) {
                revenueInput.style.display = '';
            }
            if (schoolSelect) {
                toggleSchoolTypeCustomInput(schoolSelect);
            }
            return;
        }

        if (schoolReadonly) {
            schoolReadonly.textContent = schoolTypeLabel(schoolSelect, customInput);
            schoolReadonly.style.display = '';
        }
        if (editor) {
            editor.style.display = 'none';
        }
        if (participantsReadonly) {
            participantsReadonly.textContent = participantsInput && participantsInput.value ? participantsInput.value : '0';
            participantsReadonly.style.display = '';
        }
        if (participantsInput) {
            participantsInput.style.display = 'none';
        }
        if (revenueReadonly) {
            const revenueValue = revenueInput && revenueInput.value ? parseInt(revenueInput.value, 10) : 0;
            revenueReadonly.textContent = Number.isNaN(revenueValue) ? '0' : revenueValue.toLocaleString();
            revenueReadonly.style.display = '';
        }
        if (revenueInput) {
            revenueInput.style.display = 'none';
        }
    }

    function schoolTypeSelectHtml(index) {
        return `
            <div class="school-type-editor">
                <select name="items[${index}][school_type]" class="form-control school-type-select" required>
                    <option value="">선택하세요</option>
                    <option value="middle">중등</option>
                    <option value="high">고등</option>
                    <option value="custom">직접입력</option>
                </select>
                <input type="text" name="items[${index}][item_name]" class="form-control school-type-custom-input mt-2" placeholder="구분명을 입력하세요" maxlength="100" autocomplete="off" style="display:none;">
            </div>
        `;
    }

    if (addItemBtn) {
        addItemBtn.addEventListener('click', function() {
            addItemRow();
        });
    }

    if (itemsBody) {
        itemsBody.addEventListener('click', function(e) {
            if (e.target.closest('.remove-item-btn')) {
                const row = e.target.closest('tr');
                if (row) {
                    removeItemRow(row);
                }
            }

            if (e.target.closest('.edit-item-btn')) {
                const row = e.target.closest('tr');
                if (row) {
                    const isEditing = row.getAttribute('data-row-editing') === '1';
                    setRowEditingState(row, !isEditing);
                }
            }
        });

        itemsBody.addEventListener('change', function(e) {
            if (e.target.classList.contains('school-type-select')) {
                toggleSchoolTypeCustomInput(e.target);
                const row = e.target.closest('tr');
                if (row && row.getAttribute('data-row-editing') !== '1') {
                    setRowEditingState(row, false);
                }
            }
            if (e.target.tagName === 'INPUT') {
                updateEmptyRowVisibility();
            }
        });

        itemsBody.addEventListener('input', function(e) {
            if (e.target.classList.contains('revenue-input')) {
                calculateTotalRevenue();
            }
        });

        itemsBody.querySelectorAll('tr:not(.empty-row)').forEach(function(row) {
            const schoolSelect = row.querySelector('.school-type-select');
            if (schoolSelect) {
                toggleSchoolTypeCustomInput(schoolSelect);
            }
            const isEditing = row.getAttribute('data-row-editing') === '1';
            setRowEditingState(row, isEditing);
        });
    }

    function addItemRow() {
        const emptyRow = itemsBody.querySelector('.empty-row');
        if (emptyRow) {
            emptyRow.style.display = 'none';
        }

        const currentIndex = window.itemIndex;
        const newRow = document.createElement('tr');
        newRow.setAttribute('data-item-index', currentIndex);
        newRow.setAttribute('data-row-editing', '1');
        newRow.innerHTML = `
            <td>
            <div class="school-type-readonly" style="display:none;"></div>
            ${schoolTypeSelectHtml(currentIndex)}
            </td>
            <td>
                <div class="participants-readonly" style="display:none;">0</div>
                <input type="number" name="items[${currentIndex}][participants_count]" class="form-control participants-count" placeholder="0" min="0" value="0" data-index="${currentIndex}">
            </td>
            <td>
                <div class="revenue-readonly" style="display:none;">0</div>
                <input type="number" name="items[${currentIndex}][revenue]" class="form-control revenue-input" placeholder="0" min="0" value="0" data-index="${currentIndex}">
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

        const sel = newRow.querySelector('.school-type-select');
        if (sel) {
            toggleSchoolTypeCustomInput(sel);
        }
        setRowEditingState(newRow, true);

        const revenueInput = newRow.querySelector('.revenue-input');
        if (revenueInput) {
            revenueInput.addEventListener('input', calculateTotalRevenue);
        }

        updateEmptyRowVisibility();
    }

    function removeItemRow(row) {
        if (confirm('이 항목을 삭제하시겠습니까?')) {
            row.remove();
            updateEmptyRowVisibility();
            reindexRows();
            calculateTotalRevenue();
        }
    }

    function updateEmptyRowVisibility() {
        const rows = itemsBody.querySelectorAll('tr:not(.empty-row)');
        const emptyRow = itemsBody.querySelector('.empty-row');

        if (rows.length === 0 && emptyRow) {
            emptyRow.style.display = '';
        } else if (emptyRow) {
            emptyRow.style.display = 'none';
        }
    }

    function reindexRows() {
        const rows = itemsBody.querySelectorAll('tr:not(.empty-row)');
        rows.forEach((row, index) => {
            row.setAttribute('data-item-index', index);

            row.querySelectorAll('input, select').forEach(function(field) {
                const name = field.name;
                if (!name) {
                    return;
                }
                const match = name.match(/items\[\d+\]\[(.+)\]/);
                if (match) {
                    field.name = 'items[' + index + '][' + match[1] + ']';
                }
            });
        });

        window.itemIndex = rows.length;

        rows.forEach(function(row) {
            const sel = row.querySelector('.school-type-select');
            if (sel) {
                toggleSchoolTypeCustomInput(sel);
            }
        });
    }

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

            const itemRows = itemsBody.querySelectorAll('tr:not(.empty-row)');
            let hasError = false;

            itemRows.forEach(function(row, index) {
                if (hasError) {
                    return;
                }
                const schoolTypeSelect = row.querySelector('select[name*="[school_type]"]');
                if (schoolTypeSelect && !schoolTypeSelect.value) {
                    setRowEditingState(row, true);
                    hasError = true;
                    alert((index + 1) + '번째 항목의 구분을 선택해주세요.');
                    schoolTypeSelect.focus();
                    return;
                }
                if (schoolTypeSelect && schoolTypeSelect.value === 'custom') {
                    const customInput = row.querySelector('.school-type-custom-input');
                    if (!customInput || !customInput.value.trim()) {
                        setRowEditingState(row, true);
                        hasError = true;
                        alert((index + 1) + '번째 항목: 직접입력 구분명을 입력해주세요.');
                        if (customInput) {
                            customInput.focus();
                        }
                    }
                }
            });

            if (hasError) {
                e.preventDefault();
                return false;
            }
        });
    }

    const downloadBtn = document.getElementById('download-btn');
    if (downloadBtn && downloadBtn.tagName === 'A') {
        downloadBtn.addEventListener('click', function() {});
    } else if (downloadBtn) {
        downloadBtn.disabled = true;
    }

    updateEmptyRowVisibility();

    function calculateTotalRevenue() {
        const revenueInputs = document.querySelectorAll('.revenue-input');
        let total = 0;

        revenueInputs.forEach(function(input) {
            const value = parseFloat(input.value) || 0;
            total += value;
        });

        const totalRevenueCell = document.getElementById('total-revenue');
        if (totalRevenueCell) {
            totalRevenueCell.textContent = total.toLocaleString();
        }
    }

    calculateTotalRevenue();
});

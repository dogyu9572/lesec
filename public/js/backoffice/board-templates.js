// 커스텀 필드 카운터
let customFieldCounter = 0;

// 페이지 로드 시 초기화
document.addEventListener('DOMContentLoaded', function() {
    initializeCustomFieldCounter();
    initializeCategoryGroupToggle();
});

/**
 * 커스텀 필드 카운터 초기화
 */
function initializeCustomFieldCounter() {
    const existingFields = document.querySelectorAll('.custom-field-item');
    if (existingFields.length > 0) {
        customFieldCounter = existingFields.length;
    } else {
        customFieldCounter = 0;
    }
}

/**
 * 커스텀 필드 추가
 */
function addCustomField() {
    const container = document.getElementById('customFieldsList');
    if (!container) {
        console.error('customFieldsList를 찾을 수 없습니다!');
        return;
    }
    
    const existingFields = container.children.length;
    const fieldId = 'custom_field_' + customFieldCounter++;
    
    const fieldHtml = `
        <div class="custom-field-item" id="${fieldId}">
            <div class="custom-field-header">
                <h6>커스텀 필드 #${existingFields + 1}</h6>
                <div class="custom-field-actions">
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="moveCustomField('${fieldId}', 'up')" title="위로 이동">
                        <i class="fas fa-arrow-up"></i>
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="moveCustomField('${fieldId}', 'down')" title="아래로 이동">
                        <i class="fas fa-arrow-down"></i>
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeCustomField('${fieldId}')">
                        <i class="fas fa-trash"></i> 삭제
                    </button>
                </div>
            </div>
            <div class="board-form-row">
                <div class="board-form-col board-form-col-6">
                    <div class="board-form-group">
                        <label class="board-form-label">필드명</label>
                        <input type="text" class="board-form-control" name="custom_fields[${existingFields}][name]" placeholder="예: location">
                        <small class="board-form-text">영문, 소문자, 언더스코어(_) 사용</small>
                    </div>
                </div>
                <div class="board-form-col board-form-col-6">
                    <div class="board-form-group">
                        <label class="board-form-label">라벨</label>
                        <input type="text" class="board-form-control" name="custom_fields[${existingFields}][label]" placeholder="예: 위치">
                    </div>
                </div>
            </div>
            <div class="board-form-row">
                <div class="board-form-col board-form-col-6">
                    <div class="board-form-group">
                        <label class="board-form-label">필드 타입</label>
                        <select class="board-form-control" name="custom_fields[${existingFields}][type]">
                            <option value="text">텍스트</option>
                            <option value="textarea">긴 텍스트</option>
                            <option value="number">숫자</option>
                            <option value="date">날짜</option>
                            <option value="select">선택 (드롭다운)</option>
                            <option value="radio">라디오 버튼</option>
                            <option value="checkbox">체크박스</option>
                        </select>
                    </div>
                </div>
                <div class="board-form-col board-form-col-6">
                    <div class="board-form-group">
                        <label class="board-form-label">최대 길이</label>
                        <input type="number" class="board-form-control" name="custom_fields[${existingFields}][max_length]" placeholder="예: 100">
                    </div>
                </div>
            </div>
            <div class="board-form-row">
                <div class="board-form-col board-form-col-6">
                    <div class="board-form-group">
                        <label class="board-form-label">옵션</label>
                        <input type="text" class="board-form-control" name="custom_fields[${existingFields}][options]" placeholder="예: 옵션1,옵션2,옵션3">
                        <small class="board-form-text">select, radio, checkbox 타입에서 쉼표로 구분</small>
                    </div>
                </div>
                <div class="board-form-col board-form-col-6">
                    <div class="board-form-group">
                        <label class="board-form-label">플레이스홀더</label>
                        <input type="text" class="board-form-control" name="custom_fields[${existingFields}][placeholder]" placeholder="예: 위치를 입력하세요">
                    </div>
                </div>
            </div>
            <div class="board-form-row">
                <div class="board-form-col board-form-col-6">
                    <div class="board-form-group">
                        <div class="board-checkbox-group">
                            <input type="checkbox" id="${fieldId}_required" name="custom_fields[${existingFields}][required]" value="1">
                            <label for="${fieldId}_required">필수 입력</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', fieldHtml);
    updateCustomFieldsHiddenInput();
}

/**
 * 기존 데이터로 커스텀 필드 추가
 */
function addCustomFieldWithData(fieldData) {
    const container = document.getElementById('customFieldsList');
    if (!container) {
        console.error('customFieldsList를 찾을 수 없습니다!');
        return;
    }
    
    const existingFields = container.children.length;
    const fieldId = 'custom_field_' + customFieldCounter++;
    
    const fieldHtml = `
        <div class="custom-field-item" id="${fieldId}">
            <div class="custom-field-header">
                <h6>커스텀 필드 #${existingFields + 1}</h6>
                <div class="custom-field-actions">
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="moveCustomField('${fieldId}', 'up')" title="위로 이동">
                        <i class="fas fa-arrow-up"></i>
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="moveCustomField('${fieldId}', 'down')" title="아래로 이동">
                        <i class="fas fa-arrow-down"></i>
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeCustomField('${fieldId}')">
                        <i class="fas fa-trash"></i> 삭제
                    </button>
                </div>
            </div>
            <div class="board-form-row">
                <div class="board-form-col board-form-col-6">
                    <div class="board-form-group">
                        <label class="board-form-label">필드명</label>
                        <input type="text" class="board-form-control" name="custom_fields[${existingFields}][name]" value="${fieldData.name || ''}" placeholder="예: location">
                        <small class="board-form-text">영문, 소문자, 언더스코어(_) 사용</small>
                    </div>
                </div>
                <div class="board-form-col board-form-col-6">
                    <div class="board-form-group">
                        <label class="board-form-label">라벨</label>
                        <input type="text" class="board-form-control" name="custom_fields[${existingFields}][label]" value="${fieldData.label || ''}" placeholder="예: 위치">
                    </div>
                </div>
            </div>
            <div class="board-form-row">
                <div class="board-form-col board-form-col-6">
                    <div class="board-form-group">
                        <label class="board-form-label">필드 타입</label>
                        <select class="board-form-control" name="custom_fields[${existingFields}][type]">
                            <option value="text" ${fieldData.type === 'text' ? 'selected' : ''}>텍스트</option>
                            <option value="textarea" ${fieldData.type === 'textarea' ? 'selected' : ''}>긴 텍스트</option>
                            <option value="number" ${fieldData.type === 'number' ? 'selected' : ''}>숫자</option>
                            <option value="date" ${fieldData.type === 'date' ? 'selected' : ''}>날짜</option>
                            <option value="select" ${fieldData.type === 'select' ? 'selected' : ''}>선택 (드롭다운)</option>
                            <option value="radio" ${fieldData.type === 'radio' ? 'selected' : ''}>라디오 버튼</option>
                            <option value="checkbox" ${fieldData.type === 'checkbox' ? 'selected' : ''}>체크박스</option>
                        </select>
                    </div>
                </div>
                <div class="board-form-col board-form-col-6">
                    <div class="board-form-group">
                        <label class="board-form-label">최대 길이</label>
                        <input type="number" class="board-form-control" name="custom_fields[${existingFields}][max_length]" value="${fieldData.max_length || ''}" placeholder="예: 100">
                    </div>
                </div>
            </div>
            <div class="board-form-row">
                <div class="board-form-col board-form-col-6">
                    <div class="board-form-group">
                        <label class="board-form-label">옵션</label>
                        <input type="text" class="board-form-control" name="custom_fields[${existingFields}][options]" value="${fieldData.options || ''}" placeholder="예: 옵션1,옵션2,옵션3">
                        <small class="board-form-text">select, radio, checkbox 타입에서 쉼표로 구분</small>
                    </div>
                </div>
                <div class="board-form-col board-form-col-6">
                    <div class="board-form-group">
                        <label class="board-form-label">플레이스홀더</label>
                        <input type="text" class="board-form-control" name="custom_fields[${existingFields}][placeholder]" value="${fieldData.placeholder || ''}" placeholder="예: 위치를 입력하세요">
                    </div>
                </div>
            </div>
            <div class="board-form-row">
                <div class="board-form-col board-form-col-6">
                    <div class="board-form-group">
                        <div class="board-checkbox-group">
                            <input type="checkbox" id="${fieldId}_required" name="custom_fields[${existingFields}][required]" value="1" ${fieldData.required ? 'checked' : ''}>
                            <label for="${fieldId}_required">필수 입력</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', fieldHtml);
    updateCustomFieldsHiddenInput();
}

/**
 * 커스텀 필드 삭제
 */
function removeCustomField(fieldId) {
    const field = document.getElementById(fieldId);
    if (field) {
        if (confirm('이 커스텀 필드를 삭제하시겠습니까?')) {
            field.remove();
            updateFieldNumbers();
            updateCustomFieldsHiddenInput();
        }
    }
}

/**
 * 커스텀 필드 이동
 */
function moveCustomField(fieldId, direction) {
    const fieldElement = document.getElementById(fieldId);
    if (!fieldElement) return;
    
    if (direction === 'up') {
        const prevElement = fieldElement.previousElementSibling;
        if (prevElement) {
            fieldElement.parentNode.insertBefore(fieldElement, prevElement);
        }
    } else if (direction === 'down') {
        const nextElement = fieldElement.nextElementSibling;
        if (nextElement) {
            fieldElement.parentNode.insertBefore(nextElement, fieldElement);
        }
    }
    
    updateFieldNumbers();
    updateCustomFieldsHiddenInput();
}

/**
 * 필드 번호 업데이트
 */
function updateFieldNumbers() {
    const fields = document.querySelectorAll('.custom-field-item');
    fields.forEach((field, index) => {
        const header = field.querySelector('.custom-field-header h6');
        if (header) {
            header.textContent = `커스텀 필드 #${index + 1}`;
        }
        
        // input name 속성 업데이트
        const inputs = field.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            const name = input.getAttribute('name');
            if (name && name.includes('custom_fields[')) {
                const newName = name.replace(/custom_fields\[\d+\]/, `custom_fields[${index}]`);
                input.setAttribute('name', newName);
            }
        });
    });
}

/**
 * 커스텀 필드 상태를 추적하는 히든 인풋 업데이트
 */
function updateCustomFieldsHiddenInput() {
    // 현재는 필요 없지만 향후 확장성을 위해 유지
}

/**
 * 카테고리 그룹 설정 토글 초기화
 */
function initializeCategoryGroupToggle() {
    const enableCategoryCheckbox = document.getElementById('enable_category');
    const categoryIdWrapper = document.getElementById('category_id_wrapper');
    const categoryIdSelect = document.getElementById('category_id');
    
    if (!enableCategoryCheckbox || !categoryIdWrapper) {
        return;
    }
    
    // 초기 상태 설정
    function toggleCategoryGroup() {
        if (enableCategoryCheckbox.checked) {
            categoryIdWrapper.style.display = 'block';
        } else {
            categoryIdWrapper.style.display = 'none';
            if (categoryIdSelect) {
                categoryIdSelect.value = '';
            }
        }
    }
    
    // 초기 상태 확인
    toggleCategoryGroup();
    
    // 체크박스 변경 시 토글
    enableCategoryCheckbox.addEventListener('change', toggleCategoryGroup);
}


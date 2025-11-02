// 카테고리 관리 스크립트
document.addEventListener('DOMContentLoaded', function() {
    // 카테고리 리스트 Sortable 초기화
    initCategorySortable();
    
    // 알림 메시지 자동 숨김
    initAlertMessages();
    
    // 이벤트 리스너 초기화
    initEventListeners();
});

// 이벤트 리스너 초기화
function initEventListeners() {
    // 추가 버튼
    const addBtn = document.getElementById('addCategoryBtn');
    if (addBtn) {
        addBtn.addEventListener('click', openAddModal);
    }
    
    // 수정 버튼들
    document.querySelectorAll('.edit-category-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const categoryId = this.dataset.categoryId;
            if (categoryId) {
                openEditModal(parseInt(categoryId));
            }
        });
    });
    
    // 접기/펼치기 토글
    document.querySelectorAll('.expand-toggle').forEach(btn => {
        btn.addEventListener('click', function() {
            const categoryId = this.dataset.categoryId;
            if (categoryId) {
                toggleExpand(parseInt(categoryId));
            }
        });
    });
    
    // 모달 닫기 버튼들
    const modalOverlay = document.getElementById('categoryModalOverlay');
    const modalClose = document.getElementById('categoryModalClose');
    const closeBtn = document.getElementById('closeCategoryModalBtn');
    
    if (modalOverlay) {
        modalOverlay.addEventListener('click', closeCategoryModal);
    }
    if (modalClose) {
        modalClose.addEventListener('click', closeCategoryModal);
    }
    if (closeBtn) {
        closeBtn.addEventListener('click', closeCategoryModal);
    }
    
    // 모달 저장 버튼
    const saveBtn = document.getElementById('saveCategoryModalBtn');
    if (saveBtn) {
        saveBtn.addEventListener('click', saveCategoryModal);
    }
    
    // 모달 내부 이벤트
    const depthTypeSelect = document.getElementById('modalDepthType');
    if (depthTypeSelect) {
        depthTypeSelect.addEventListener('change', onDepthTypeChange);
    }
    
    const groupSelect = document.getElementById('modalGroupId');
    if (groupSelect) {
        groupSelect.addEventListener('change', onGroupChange);
    }
    
    const parentCategorySelect = document.getElementById('modalParentCategoryId');
    if (parentCategorySelect) {
        parentCategorySelect.addEventListener('change', function() {
            const parentIdInput = document.getElementById('modalParentId');
            if (parentIdInput) {
                parentIdInput.value = this.value || '';
            }
        });
    }
}

// 카테고리 Sortable 초기화
function initCategorySortable() {
    const categoryList = document.getElementById('categoryList');
    if (!categoryList) return;

    // 1차 카테고리 Sortable 초기화
    new Sortable(categoryList, {
        animation: 300,
        easing: "cubic-bezier(1, 0, 0, 1)",
        handle: '.drag-handle',
        delay: 100,
        delayOnTouchOnly: true,
        touchStartThreshold: 5,
        ghostClass: 'sortable-ghost',
        chosenClass: 'sortable-chosen',
        dragClass: 'sortable-drag',
        fallbackTolerance: 5,
        group: 'category-group',
        draggable: '.category-menu-item-depth-1',
        onStart: function() {
            document.body.style.cursor = 'grabbing';
        },
        onEnd: function(evt) {
            document.body.style.cursor = '';
            
            // 1차 카테고리만 처리
            const movedItem = evt.item;
            const movedDepth = parseInt(movedItem.dataset.depth);
            
            if (movedDepth === 1) {
                const siblings = Array.from(evt.to.children).filter(item => 
                    parseInt(item.dataset.depth) === movedDepth
                );
                
                // 순서 데이터 수집
                const orders = siblings.map((item, index) => ({
                    id: parseInt(item.dataset.id),
                    order: index
                }));
                
                // 서버에 순서 업데이트 요청
                updateCategoryOrder(orders);
            }
        }
    });

    // 2차 카테고리 Sortable 초기화 (동적으로 생성된 것들도 포함)
    initDepth2Sortables();
}

// 2차 카테고리 Sortable 초기화
function initDepth2Sortables() {
    document.querySelectorAll('.category-children').forEach(function(childrenContainer) {
        // 이미 Sortable이 초기화된 경우 건너뜀
        if (Sortable.get(childrenContainer)) {
            return;
        }

        new Sortable(childrenContainer, {
            animation: 300,
            easing: "cubic-bezier(1, 0, 0, 1)",
            handle: '.drag-handle',
            delay: 100,
            delayOnTouchOnly: true,
            touchStartThreshold: 5,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'sortable-drag',
            fallbackTolerance: 5,
            group: 'category-group',
            draggable: '.category-menu-item-depth-2',
            onStart: function() {
                document.body.style.cursor = 'grabbing';
            },
            onEnd: function(evt) {
                document.body.style.cursor = '';
                
                // 2차 카테고리만 처리
                const movedItem = evt.item;
                const movedDepth = parseInt(movedItem.dataset.depth);
                
                if (movedDepth === 2) {
                    const siblings = Array.from(evt.to.children).filter(item => 
                        parseInt(item.dataset.depth) === movedDepth
                    );
                    
                    // 순서 데이터 수집
                    const orders = siblings.map((item, index) => ({
                        id: parseInt(item.dataset.id),
                        order: index
                    }));
                    
                    // 서버에 순서 업데이트 요청
                    updateCategoryOrder(orders);
                }
            }
        });
    });
}

// 카테고리 순서 업데이트 (AJAX)
function updateCategoryOrder(orders) {
    // 내림차순으로 정렬되어 있으므로 역순으로 매핑
    const orderData = {};
    const totalItems = orders.length;
    orders.forEach((item, index) => {
        // 첫 번째 항목이 display_order가 가장 큰 값
        // 마지막 항목이 display_order가 가장 작은 값
        orderData[totalItems - index - 1] = item.id;
    });

    fetch('/backoffice/categories/update-order', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ orders: orderData })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showOrderSavedMessage();
        } else {
            alert('순서 변경 중 오류가 발생했습니다.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('순서 변경 중 오류가 발생했습니다.');
    });
}

// 순서 저장 메시지 표시
function showOrderSavedMessage() {
    const message = document.getElementById('orderSavedMessage');
    if (message) {
        message.style.display = 'flex';
        setTimeout(() => {
            message.style.display = 'none';
        }, 2000);
    }
}

// 알림 메시지 자동 숨김
function initAlertMessages() {
    const alerts = document.querySelectorAll('.hidden-alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.3s';
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.style.display = 'none';
            }, 300);
        }, 3000);
    });
}


// 접기/펼치기 토글 (사이드바 스타일)
function toggleExpand(categoryId) {
    const menuItem = document.querySelector(`[data-id="${categoryId}"]`);
    if (!menuItem) return;
    
    const childrenContainer = document.getElementById('children-' + categoryId);
    if (!childrenContainer) return;
    
    const isExpanded = menuItem.classList.contains('expanded');
    
    if (isExpanded) {
        // 접기
        menuItem.classList.remove('expanded');
        menuItem.classList.add('collapsed');
        childrenContainer.style.maxHeight = '0';
    } else {
        // 펼치기
        menuItem.classList.remove('collapsed');
        menuItem.classList.add('expanded');
        // 실제 높이 계산하여 설정
        childrenContainer.style.maxHeight = childrenContainer.scrollHeight + 'px';
    }
}

// 인라인 편집 모드 활성화 (수정 버튼 클릭 시)
function enableInlineEditMode(categoryId) {
    const menuItem = document.querySelector(`[data-id="${categoryId}"]`);
    if (!menuItem) return;
    
    // 이미 편집 모드인 경우 무시
    if (menuItem.classList.contains('inline-edit-mode')) return;
    
    menuItem.classList.add('inline-edit-mode');
    
    // 코드 필드 찾기 (모든 뎁스에서 code 사용)
    const codeElement = document.getElementById(`code-${categoryId}`);
    
    const nameElement = document.getElementById(`name-${categoryId}`);
    
    // 코드 필드 인라인 편집 활성화
    if (codeElement) {
        const originalCode = codeElement.textContent.trim();
        const input = document.createElement('input');
        input.type = 'text';
        input.value = originalCode === '-' ? '' : originalCode;
        input.className = 'inline-edit-input';
        input.dataset.original = originalCode;
        input.dataset.field = codeElement.dataset.field;
        
        const controls = createInlineControls(categoryId, codeElement.dataset.field, input);
        
        codeElement.innerHTML = '';
        codeElement.appendChild(input);
        codeElement.appendChild(controls);
        input.focus();
        input.select();
        
        // Enter 키로 저장, ESC로 취소
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                saveInlineEditFromMode(categoryId, codeElement.dataset.field, input);
            } else if (e.key === 'Escape') {
                cancelInlineEditFromMode(codeElement, originalCode);
            }
        });
    }
    
    // 명칭 필드 인라인 편집 활성화
    if (nameElement) {
        const originalName = nameElement.textContent.trim();
        const input = document.createElement('input');
        input.type = 'text';
        input.value = originalName;
        input.className = 'inline-edit-input';
        input.dataset.original = originalName;
        input.dataset.field = 'name';
        
        const controls = createInlineControls(categoryId, 'name', input);
        
        nameElement.innerHTML = '';
        nameElement.appendChild(input);
        nameElement.appendChild(controls);
        
        // 코드 필드가 없으면 명칭 필드에 포커스
        if (!codeElement) {
            input.focus();
            input.select();
        }
        
        // Enter 키로 저장, ESC로 취소
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                saveInlineEditFromMode(categoryId, 'name', input);
            } else if (e.key === 'Escape') {
                cancelInlineEditFromMode(nameElement, originalName);
            }
        });
    }
}

// 인라인 편집 컨트롤 생성
function createInlineControls(categoryId, field, inputElement) {
    const controls = document.createElement('span');
    controls.className = 'inline-edit-controls';
    controls.innerHTML = `
        <button type="button" class="btn-save" onclick="saveInlineEditFromMode(${categoryId}, '${field}', this.previousElementSibling)">
            <i class="fas fa-check"></i>
        </button>
        <button type="button" class="btn-cancel" onclick="cancelInlineEditFromMode(this, '${inputElement.dataset.original}')">
            <i class="fas fa-times"></i>
        </button>
    `;
    return controls;
}

// 인라인 편집 모드에서 저장
function saveInlineEditFromMode(categoryId, field, inputElement) {
    if (!inputElement) {
        inputElement = event.target.previousElementSibling;
    }
    
    const newValue = inputElement.value.trim();
    const data = {};
    data[field] = newValue;
    
    fetch(`/backoffice/categories/${categoryId}/update-inline`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            // 페이지 새로고침
            setTimeout(() => {
                window.location.reload();
            }, 500);
        } else {
            showToast(data.message || '저장 중 오류가 발생했습니다.', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('저장 중 오류가 발생했습니다.', 'error');
    });
}

// 인라인 편집 모드에서 취소
function cancelInlineEditFromMode(element, originalValue) {
    if (!element || !element.dataset || !element.dataset.field) {
        // element가 클릭된 버튼인 경우
        const fieldElement = element.closest('[data-field]');
        if (!fieldElement) return;
        
        const menuItem = fieldElement.closest('.category-menu-item');
        if (!menuItem) return;
        
        const field = fieldElement.dataset.field;
        const categoryId = menuItem.dataset.id;
        
        if (field === 'code') {
            const codeElement = document.getElementById(`code-${categoryId}`);
            if (codeElement) {
                codeElement.innerHTML = originalValue === '-' ? '' : originalValue;
            }
        } else if (field === 'name') {
            const nameElement = document.getElementById(`name-${categoryId}`);
            if (nameElement) {
                nameElement.innerHTML = originalValue;
            }
        }
        
        // 모든 필드가 원래대로 돌아갔는지 확인
        const hasEditing = menuItem.querySelector('.inline-edit-input');
        if (!hasEditing) {
            menuItem.classList.remove('inline-edit-mode');
        }
    } else {
        // element가 필드 요소인 경우
        const menuItem = element.closest('.category-menu-item');
        const field = element.dataset.field;
        const categoryId = menuItem.dataset.id;
        
        if (field === 'code') {
            const codeElement = document.getElementById(`code-${categoryId}`);
            if (codeElement) {
                codeElement.innerHTML = originalValue === '-' ? '' : originalValue;
            }
        } else if (field === 'name') {
            const nameElement = document.getElementById(`name-${categoryId}`);
            if (nameElement) {
                nameElement.innerHTML = originalValue;
            }
        }
        
        // 모든 필드가 원래대로 돌아갔는지 확인
        const hasEditing = menuItem.querySelector('.inline-edit-input');
        if (!hasEditing) {
            menuItem.classList.remove('inline-edit-mode');
        }
    }
}



// 단일 추가 모달 열기
function openAddModal() {
    const modal = document.getElementById('categoryModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalForm = document.getElementById('categoryModalForm');
    const depthTypeSelect = document.getElementById('modalDepthType');
    
    modalTitle.textContent = '카테고리 추가';
    modalForm.reset();
    
    // 수정 모드가 아님을 명시
    document.getElementById('modalCategoryId').value = '';
    
    // 기본값: 선택된 그룹이 있으면 1차로 자동 선택
    // 선택된 그룹 ID는 페이지에서 전역 변수로 제공됨
    const selectedGroupId = window.selectedGroupId || null;
    if (selectedGroupId) {
        depthTypeSelect.value = '1';
        onDepthTypeChange();
        document.getElementById('modalGroupId').value = selectedGroupId;
    } else {
        depthTypeSelect.value = '0';
        onDepthTypeChange();
    }
    
    // 미리 생성된 코드 표시
    generatePreviewCode();
    
    document.getElementById('modalError').style.display = 'none';
    modal.style.display = 'flex';
}

// 미리 생성될 코드 표시
function generatePreviewCode() {
    fetch('/backoffice/categories/generate-preview-code', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({})
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.code) {
            document.getElementById('modalCode').value = data.code;
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

// 수정 모달 열기
function openEditModal(categoryId) {
    const modal = document.getElementById('categoryModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalForm = document.getElementById('categoryModalForm');
    
    modalTitle.textContent = '카테고리 수정';
    modalForm.reset();
    
    // 수정 모드임을 명시
    document.getElementById('modalCategoryId').value = categoryId;
    
    // AJAX로 카테고리 정보 로드
    fetch(`/backoffice/categories/${categoryId}/edit-data`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.category) {
            const cat = data.category;
            
            // 수정 모드에서는 depth_type 필드 숨김
            const depthTypeWrapper = document.getElementById('modalDepthType').closest('.modal-form-group');
            if (depthTypeWrapper) depthTypeWrapper.style.display = 'none';
            
            const groupSelectWrapper = document.getElementById('groupSelectWrapper');
            if (groupSelectWrapper) groupSelectWrapper.style.display = 'none';
            
            const parentCategoryWrapper = document.getElementById('parentCategorySelectWrapper');
            if (parentCategoryWrapper) parentCategoryWrapper.style.display = 'none';
            
            // 값 채우기
            document.getElementById('modalName').value = cat.name || '';
            document.getElementById('modalCode').value = cat.code || '';
            document.getElementById('modalDisplayOrder').value = cat.display_order || 0;
            document.getElementById('modalIsActive').value = cat.is_active ? '1' : '0';
            
        } else {
            showModalError('카테고리 정보를 불러올 수 없습니다.');
            return;
        }
        
        document.getElementById('modalError').style.display = 'none';
        modal.style.display = 'flex';
    })
    .catch(error => {
        console.error('Error:', error);
        showModalError('카테고리 정보를 불러올 수 없습니다.');
    });
}

// 하위추가 버튼용: 특정 부모가 있는 모달 열기
function openAddModalWithParent(parentId) {
    const modal = document.getElementById('categoryModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalForm = document.getElementById('categoryModalForm');
    const depthTypeSelect = document.getElementById('modalDepthType');
    
    modalTitle.textContent = '카테고리 추가';
    modalForm.reset();
    
    // 부모 ID로부터 depth 판단
    const parentElement = document.querySelector(`[data-id="${parentId}"]`);
    if (parentElement) {
        const parentDepth = parseInt(parentElement.dataset.depth || '0');
        
        if (parentDepth === 0) {
            // 부모가 그룹이면 1차 추가
            depthTypeSelect.value = '1';
            onDepthTypeChange();
            document.getElementById('modalGroupId').value = parentId;
        } else if (parentDepth === 1) {
            // 부모가 1차면 2차 추가
            depthTypeSelect.value = '2';
            onDepthTypeChange();
            
            // 부모의 그룹 찾기
            const parentCategory = parentElement.closest('.category-menu-item');
            const groupId = getGroupIdFromParent(parentId);
            if (groupId) {
                document.getElementById('modalGroupId').value = groupId;
                onGroupChange();
                document.getElementById('modalParentCategoryId').value = parentId;
                document.getElementById('modalParentId').value = parentId;
            }
        }
    }
    
    // 미리 생성된 코드 표시
    generatePreviewCode();
    
    document.getElementById('modalError').style.display = 'none';
    modal.style.display = 'flex';
}

// 부모 ID로부터 그룹 ID 찾기
function getGroupIdFromParent(parentId) {
    // 현재 URL에서 group 파라미터 추출
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get('group') || null;
}

// 뎁스 타입 변경 시
function onDepthTypeChange() {
    const depthType = document.getElementById('modalDepthType').value;
    const groupWrapper = document.getElementById('groupSelectWrapper');
    const parentWrapper = document.getElementById('parentCategorySelectWrapper');
    const parentIdInput = document.getElementById('modalParentId');
    
    if (depthType === '0') {
        // 그룹 추가: 부모 선택 불필요
        groupWrapper.style.display = 'none';
        parentWrapper.style.display = 'none';
        if (parentIdInput) parentIdInput.value = '';
    } else if (depthType === '1') {
        // 1차 추가: 그룹 선택 필요
        groupWrapper.style.display = 'block';
        parentWrapper.style.display = 'none';
        onGroupChange(); // 그룹 선택 시 1차 목록 업데이트
    } else if (depthType === '2') {
        // 2차 추가: 그룹 + 1차 선택 필요
        groupWrapper.style.display = 'block';
        parentWrapper.style.display = 'block';
        onGroupChange();
    }
}

// 그룹 선택 시 해당 그룹의 1차 카테고리 로드
function onGroupChange() {
    const groupId = document.getElementById('modalGroupId').value;
    const parentSelect = document.getElementById('modalParentCategoryId');
    const parentIdInput = document.getElementById('modalParentId');
    const depthType = document.getElementById('modalDepthType').value;
    
    if (!groupId) {
        if (parentSelect) parentSelect.innerHTML = '<option value="">1차 카테고리 선택</option>';
        if (parentIdInput) parentIdInput.value = '';
        return;
    }
    
    if (depthType === '1') {
        // 1차 추가 시: 그룹을 부모로 설정
        if (parentIdInput) parentIdInput.value = groupId;
        return;
    }
    
    // 2차 추가 시: AJAX로 해당 그룹의 1차 카테고리 목록 로드
    if (depthType === '2' && parentSelect) {
        parentSelect.innerHTML = '<option value="">로딩 중...</option>';
        parentSelect.disabled = true;
        
        // AJAX로 해당 그룹의 1차 카테고리 가져오기
        fetch(`/backoffice/categories/get-by-group/${groupId}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            parentSelect.innerHTML = '<option value="">1차 카테고리 선택</option>';
            parentSelect.disabled = false;
            
            if (data && data.length > 0) {
                data.forEach(category => {
                    const option = document.createElement('option');
                    option.value = category.id;
                    option.textContent = category.name;
                    parentSelect.appendChild(option);
                });
                
                // 첫 번째 1차 카테고리를 기본값으로 선택 (사용자 편의)
                if (data.length > 0) {
                    const firstCategoryId = data[0].id;
                    if (parentIdInput) parentIdInput.value = firstCategoryId;
                    parentSelect.value = firstCategoryId;
                }
            }
        })
        .catch(error => {
            console.error('1차 카테고리 로드 오류:', error);
            parentSelect.innerHTML = '<option value="">1차 카테고리 선택</option>';
            parentSelect.disabled = false;
        });
    }
}


// 모달 닫기
function closeCategoryModal() {
    const modal = document.getElementById('categoryModal');
    modal.style.display = 'none';
    
    // 수정 모드에서 숨긴 필드들 다시 표시
    const depthTypeWrapper = document.getElementById('modalDepthType').closest('.modal-form-group');
    if (depthTypeWrapper) depthTypeWrapper.style.display = 'block';
    
    const groupSelectWrapper = document.getElementById('groupSelectWrapper');
    if (groupSelectWrapper) groupSelectWrapper.style.display = 'block';
    
    const parentCategoryWrapper = document.getElementById('parentCategorySelectWrapper');
    if (parentCategoryWrapper) parentCategoryWrapper.style.display = 'block';
}

// 모달 저장
function saveCategoryModal() {
    const form = document.getElementById('categoryModalForm');
    const formData = new FormData(form);
    const data = {};
    
    for (let [key, value] of formData.entries()) {
        if (key === 'is_active') {
            data[key] = value === '1';
        } else if (key === 'display_order') {
            data[key] = value ? parseInt(value) : 0;
        } else {
            data[key] = value;
        }
    }
    
    const categoryId = document.getElementById('modalCategoryId').value;
    
    // 수정 모드인지 확인
    if (categoryId) {
        // 수정 모드
        data.category_id = categoryId;
        
        // 필수 필드 검사
        if (!data.name || !data.name.trim()) {
            showModalError('명칭을 입력해주세요.');
            return;
        }
        
        fetch('/backoffice/categories/update-modal', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                showToast(result.message || '카테고리가 수정되었습니다.', 'success');
                closeCategoryModal();
                
                // 현재 선택된 그룹 유지
                const urlParams = new URLSearchParams(window.location.search);
                const currentGroup = urlParams.get('group');
                const reloadUrl = currentGroup ? `?group=${currentGroup}` : '';
                
                setTimeout(() => {
                    window.location.href = window.location.pathname + reloadUrl;
                }, 500);
            } else {
                showModalError(result.message || '수정 중 오류가 발생했습니다.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showModalError('수정 중 오류가 발생했습니다.');
        });
    } else {
        // 추가 모드
        // 필수 필드 검사
        if (!data.name || !data.name.trim()) {
            showModalError('명칭을 입력해주세요.');
            return;
        }
        
        // depth_type이 없으면 기존 방식 유지
        if (!data.depth_type) {
            // 기존 로직
        } else {
            // depth_type에 따른 검증
            if (data.depth_type === '1' && !data.group_id) {
                showModalError('그룹을 선택해주세요.');
                return;
            }
            if (data.depth_type === '2' && (!data.group_id || !data.parent_category_id)) {
                showModalError('그룹과 1차 카테고리를 선택해주세요.');
                return;
            }
        }
        
        fetch('/backoffice/categories/store-modal', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                showToast(result.message || '카테고리가 등록되었습니다.', 'success');
                closeCategoryModal();
                
                // 현재 선택된 그룹 유지
                const urlParams = new URLSearchParams(window.location.search);
                const currentGroup = urlParams.get('group');
                const reloadUrl = currentGroup ? `?group=${currentGroup}` : '';
                
                setTimeout(() => {
                    window.location.href = window.location.pathname + reloadUrl;
                }, 500);
            } else {
                showModalError(result.message || '등록 중 오류가 발생했습니다.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showModalError('등록 중 오류가 발생했습니다.');
        });
    }
}

// 모달 에러 표시
function showModalError(message) {
    const errorDiv = document.getElementById('modalError');
    errorDiv.textContent = message;
    errorDiv.style.display = 'block';
}

// 토스트 메시지 표시
function showToast(message, type = 'success') {
    const toast = document.getElementById('toastMessage');
    if (!toast) return;
    
    toast.textContent = message;
    toast.className = 'toast-message ' + (type === 'error' ? 'error' : '');
    toast.style.display = 'block';
    
    setTimeout(() => {
        toast.style.display = 'none';
    }, 3000);
}



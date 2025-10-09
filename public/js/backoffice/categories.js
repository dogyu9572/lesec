// 카테고리 관리 스크립트
document.addEventListener('DOMContentLoaded', function() {
    // 카테고리 리스트 Sortable 초기화
    initCategorySortable();
    
    // 알림 메시지 자동 숨김
    initAlertMessages();
    
    // 카테고리 생성/편집 폼 초기화
    initCategoryForm();
});

// 카테고리 Sortable 초기화
function initCategorySortable() {
    const categoryList = document.getElementById('categoryList');
    if (!categoryList) return;

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
        onStart: function() {
            document.body.style.cursor = 'grabbing';
        },
        onEnd: function(evt) {
            document.body.style.cursor = '';
            
            // 같은 depth 내에서만 순서 변경 가능하도록 체크
            const movedItem = evt.item;
            const movedDepth = parseInt(movedItem.dataset.depth);
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
    });
}

// 카테고리 순서 업데이트 (AJAX)
function updateCategoryOrder(orders) {
    const orderData = {};
    orders.forEach(item => {
        orderData[item.order] = item.id;
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

// 카테고리 폼 초기화 (생성/편집 공통)
function initCategoryForm() {
    const groupSelect = document.getElementById('category_group_select');
    const newGroupInput = document.getElementById('new_category_group');
    const form = document.querySelector('form');
    
    // 그룹 선택 이벤트 (생성 페이지만)
    if (groupSelect) {
        groupSelect.addEventListener('change', toggleNewGroupInput);
    }
    
    // 새 그룹 입력 이벤트 (생성 페이지만)
    if (newGroupInput) {
        newGroupInput.addEventListener('input', updateNewGroupValue);
    }
    
    // 폼 제출 이벤트
    if (form) {
        form.addEventListener('submit', function(e) {
            if (!validateCategoryForm()) {
                e.preventDefault();
            }
        });
    }
    
    // 부모 카테고리 변경 이벤트 (편집 페이지)
    const parentSelect = document.getElementById('parent_id');
    if (parentSelect) {
        parentSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const parentDepth = parseInt(selectedOption.dataset.depth || 0);
            
            if (parentDepth >= 3) {
                alert('3단계 카테고리는 하위 카테고리를 생성할 수 없습니다.');
                // 편집 페이지에서는 원래 값으로 되돌리기
                const originalValue = this.dataset.originalValue || '';
                this.value = originalValue;
            }
        });
    }
}

// 새 그룹 입력 토글 (생성 페이지)
function toggleNewGroupInput() {
    const select = document.getElementById('category_group_select');
    const newGroupWrapper = document.getElementById('new_group_input_wrapper');
    const newGroupInput = document.getElementById('new_category_group');
    const hiddenInput = document.getElementById('category_group');
    const parentIdSelect = document.getElementById('parent_id');
    
    if (select.value === '__new__') {
        // 새 그룹 추가 선택
        newGroupWrapper.style.display = 'block';
        newGroupInput.required = true;
        newGroupInput.focus();
        parentIdSelect.disabled = true;
        parentIdSelect.value = '';
        
        // 부모 카테고리 선택 비활성화 (새 그룹이므로 1차 카테고리)
        parentIdSelect.innerHTML = '<option value="">1차 카테고리 (최상위)</option>';
    } else {
        // 기존 그룹 선택
        newGroupWrapper.style.display = 'none';
        newGroupInput.required = false;
        newGroupInput.value = '';
        hiddenInput.value = select.value;
        parentIdSelect.disabled = false;
        
        // 부모 카테고리 옵션 로드
        loadParentCategories(select.value);
    }
}

// 새 그룹 입력값을 hidden input에 설정 (생성 페이지)
function updateNewGroupValue() {
    const newGroupInput = document.getElementById('new_category_group');
    const hiddenInput = document.getElementById('category_group');
    hiddenInput.value = newGroupInput.value.trim();
}

// 부모 카테고리 옵션 로드 (생성 페이지)
function loadParentCategories(groupId) {
    const parentIdSelect = document.getElementById('parent_id');
    
    // 기본 옵션
    parentIdSelect.innerHTML = '<option value="">1차 카테고리 (최상위)</option>';
    
    if (!groupId) {
        return;
    }
    
    // AJAX로 해당 그룹의 카테고리 목록 가져오기
    fetch(`/backoffice/categories/get-by-group/${groupId}`)
        .then(response => response.json())
        .then(data => {
            data.forEach(category => {
                const option = document.createElement('option');
                option.value = category.id;
                option.textContent = category.name;
                option.dataset.depth = category.depth;
                parentIdSelect.appendChild(option);
            });
        })
        .catch(error => {
            console.error('부모 카테고리 로드 오류:', error);
        });
}

// 폼 유효성 검사 (생성/편집 공통)
function validateCategoryForm() {
    const name = document.getElementById('name');
    const groupSelect = document.getElementById('category_group_select');
    const newGroupInput = document.getElementById('new_category_group');
    const hiddenInput = document.getElementById('category_group');
    const group = document.getElementById('category_group'); // 편집 페이지용
    
    if (!name.value.trim()) {
        alert('카테고리명을 입력해주세요.');
        name.focus();
        return false;
    }
    
    // 생성 페이지인 경우
    if (groupSelect) {
        if (groupSelect.value === '__new__') {
            if (!newGroupInput.value.trim()) {
                alert('새 그룹명을 입력해주세요.');
                newGroupInput.focus();
                return false;
            }
            hiddenInput.value = newGroupInput.value.trim();
        } else {
            if (!groupSelect.value) {
                alert('카테고리 그룹을 선택해주세요.');
                groupSelect.focus();
                return false;
            }
            hiddenInput.value = groupSelect.value;
        }
    }
    
    // 편집 페이지인 경우
    if (group && !group.value.trim()) {
        alert('카테고리 그룹을 선택해주세요.');
        group.focus();
        return false;
    }
    
    return true;
}



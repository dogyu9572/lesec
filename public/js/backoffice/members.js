// 회원 관리 페이지 JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // 회원 타입(교사/학생) 선택에 따른 필드 표시/숨김
    initMemberTypeToggle();
    // 연락처 포맷팅
    initPhoneFormatting();
});

/**
 * 회원 타입 토글 기능
 */
function initMemberTypeToggle() {
    const memberTypeTabs = document.querySelectorAll('.member_type_tab');
    const studentOnlyFields = document.querySelectorAll('.student-only');
    const teacherOnlyFields = document.querySelectorAll('.teacher-only');
    const form = document.getElementById('memberForm');
    
    if (memberTypeTabs.length === 0) {
        return; // 회원 타입 탭이 없는 페이지에서는 실행하지 않음
    }
    
    function toggleStudentFields() {
        const isStudent = document.querySelector('.member_type_student:checked');
        studentOnlyFields.forEach(field => {
            field.style.display = isStudent ? 'block' : 'none';
        });
        teacherOnlyFields.forEach(field => {
            field.style.display = isStudent ? 'none' : 'block';
        });
    }
    
    // 폼 제출 시 숨겨진 필드 disabled 처리
    if (form) {
        form.addEventListener('submit', function() {
            studentOnlyFields.forEach(field => {
                if (field.style.display === 'none') {
                    const inputs = field.querySelectorAll('input, select, textarea');
                    inputs.forEach(input => input.disabled = true);
                }
            });
            teacherOnlyFields.forEach(field => {
                if (field.style.display === 'none') {
                    const inputs = field.querySelectorAll('input, select, textarea');
                    inputs.forEach(input => input.disabled = true);
                }
            });
        });
    }
    
    memberTypeTabs.forEach(tab => {
        tab.addEventListener('change', toggleStudentFields);
    });
    
    // 초기 로드시 실행
    toggleStudentFields();
}

/**
 * 연락처 포맷팅 기능
 */
function initPhoneFormatting() {
    const phoneInputs = document.querySelectorAll('[data-phone-input]');
    
    phoneInputs.forEach(function(input) {
        // 초기값 포맷팅
        const initial = formatPhone(normalizePhone(input.value));
        if (initial) {
            input.value = initial;
        }
        
        // 실시간 입력 포맷팅
        input.addEventListener('input', function(e) {
            const cursorPos = input.selectionStart;
            const oldValue = input.value;
            
            const digits = normalizePhone(oldValue);
            const formatted = formatPhone(digits);
            
            if (formatted === oldValue) {
                return;
            }
            
            input.value = formatted;
            
            // 커서 위치 보정
            const digitsBefore = normalizePhone(oldValue.substring(0, cursorPos)).length;
            let newCursorPos = 0;
            let digitCount = 0;
            
            for (let i = 0; i < formatted.length; i++) {
                if (formatted[i] !== '-') {
                    digitCount++;
                }
                if (digitCount >= digitsBefore) {
                    newCursorPos = i + 1;
                    break;
                }
            }
            
            // 하이픈 바로 뒤에 커서가 있으면 하이픈 다음으로 이동
            if (newCursorPos < formatted.length && formatted[newCursorPos] === '-') {
                newCursorPos++;
            }
            
            input.setSelectionRange(newCursorPos, newCursorPos);
        });
        
        // 포커스 아웃 시 최종 포맷팅
        input.addEventListener('blur', function() {
            const digits = normalizePhone(input.value);
            input.value = formatPhone(digits);
        });
    });
}

/**
 * 전화번호에서 숫자만 추출
 */
function normalizePhone(value) {
    return (value || '').replace(/[^0-9]/g, '');
}

/**
 * 전화번호 포맷팅 (010-1234-5678)
 */
function formatPhone(digits) {
    if (!digits) {
        return '';
    }
    
    const len = digits.length;
    
    if (len <= 3) {
        return digits;
    }
    
    if (len <= 7) {
        return digits.substring(0, 3) + '-' + digits.substring(3);
    }
    
    if (len === 10) {
        return digits.substring(0, 3) + '-' + digits.substring(3, 6) + '-' + digits.substring(6);
    }
    
    if (len === 11) {
        return digits.substring(0, 3) + '-' + digits.substring(3, 7) + '-' + digits.substring(7);
    }
    
    if (len > 11) {
        return digits.substring(0, 3) + '-' + digits.substring(3, 7) + '-' + digits.substring(7, 11);
    }
    
    return digits;
}


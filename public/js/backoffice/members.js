// 회원 관리 페이지 JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // 회원 타입(교사/학생) 선택에 따른 필드 표시/숨김
    initMemberTypeToggle();
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


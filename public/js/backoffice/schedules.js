(function() {
    'use strict';

    document.addEventListener('DOMContentLoaded', function() {
        const configEl = document.getElementById('schedule-config');
        if (!configEl) {
            return;
        }

        const baseUrl = configEl.getAttribute('data-base-url');
        const storeUrl = configEl.getAttribute('data-store-url');
        const schedulesUrl = configEl.getAttribute('data-schedules-url');
        const modal = document.getElementById('schedule-modal');
        const scheduleForm = document.getElementById('schedule-form');
        const btnRegister = document.getElementById('btn-register');
        const btnCloseModal = document.getElementById('btn-close-modal');
        const btnCancelModal = document.getElementById('btn-cancel-modal');
        const modalOverlay = document.getElementById('schedule-modal-overlay');
        const modalTitle = document.getElementById('modal-title');
        const editButtons = document.querySelectorAll('.btn-edit');
        const saveButton = document.querySelector('button[form="schedule-form"]');
        let selectedDate = null;

        function setFormLoading(isLoading) {
            if (!saveButton) {
                return;
            }
            saveButton.disabled = isLoading;
            saveButton.classList.toggle('is-loading', isLoading);
        }

        if (saveButton && scheduleForm) {
            saveButton.addEventListener('click', function(event) {
                event.preventDefault();
                scheduleForm.requestSubmit();
            });
        }

        // 등록 버튼 클릭
        if (btnRegister) {
            btnRegister.addEventListener('click', function() {
                resetForm();
                modalTitle.textContent = '일정등록';
                showModal();
            });
        }

        // 모달 닫기 버튼
        if (btnCloseModal) {
            btnCloseModal.addEventListener('click', function() {
                hideModal();
                resetForm();
            });
        }

        // 취소 버튼
        if (btnCancelModal) {
            btnCancelModal.addEventListener('click', function() {
                hideModal();
                resetForm();
            });
        }

        // 모달 외부 클릭 시 닫기
        if (modalOverlay) {
            modalOverlay.addEventListener('click', function() {
                hideModal();
                resetForm();
            });
        }

        // ESC 키로 모달 닫기
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal && modal.style.display === 'block') {
                hideModal();
                resetForm();
            }
        });

        // 수정 버튼 클릭 (동적으로 생성된 버튼도 처리)
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('btn-edit')) {
                const scheduleId = e.target.getAttribute('data-id');
                loadScheduleForEdit(scheduleId);
            }
        });

        // 폼 제출
        if (scheduleForm) {
            scheduleForm.addEventListener('submit', function(e) {
                e.preventDefault();
                submitSchedule();
            });
        }

        // 캘린더 날짜 클릭
        document.querySelectorAll('.calendar-day.clickable').forEach(function(dayEl) {
            dayEl.addEventListener('click', function() {
                const date = this.getAttribute('data-date');
                if (!date) {
                    return;
                }

                // 선택된 날짜 스타일 업데이트
                document.querySelectorAll('.calendar-day').forEach(function(el) {
                    el.classList.remove('selected');
                });
                this.classList.add('selected');

                selectedDate = date;
                loadSchedules(date);
            });
        });

        // 초기 로드 시 선택된 날짜가 있으면 일정 로드
        const urlParams = new URLSearchParams(window.location.search);
        const dateParam = urlParams.get('date');
        if (dateParam) {
            const dayEl = document.querySelector(`[data-date="${dateParam}"]`);
            if (dayEl && dayEl.classList.contains('clickable')) {
                dayEl.classList.add('selected');
                selectedDate = dateParam;
                loadSchedules(dateParam);
            }
        }

        // 월 네비게이션
        document.querySelectorAll('.arrow').forEach(function(arrow) {
            arrow.addEventListener('click', function() {
                const year = parseInt(this.getAttribute('data-year'));
                const month = parseInt(this.getAttribute('data-month'));
                let newYear = year;
                let newMonth = month;

                if (this.classList.contains('prev')) {
                    newMonth--;
                    if (newMonth < 1) {
                        newMonth = 12;
                        newYear--;
                    }
                } else if (this.classList.contains('next')) {
                    newMonth++;
                    if (newMonth > 12) {
                        newMonth = 1;
                        newYear++;
                    }
                }

                window.location.href = `${baseUrl}?year=${newYear}&month=${newMonth}`;
            });
        });

        function showModal() {
            if (modal) {
                modal.style.display = 'flex';
            }
        }

        function hideModal() {
            if (modal) {
                modal.style.display = 'none';
            }
        }

        function resetForm() {
            scheduleForm.reset();
            document.getElementById('form-method').value = 'POST';
            document.getElementById('schedule-id').value = '';
            scheduleForm.action = storeUrl;
        }

        function loadSchedules(date) {
            if (!date) {
                return;
            }

            const dateObj = new Date(date);
            const year = dateObj.getFullYear();
            const month = String(dateObj.getMonth() + 1).padStart(2, '0');
            const day = String(dateObj.getDate()).padStart(2, '0');
            const dayOfWeek = ['일', '월', '화', '수', '목', '금', '토'][dateObj.getDay()];

            // 선택된 날짜 표시 업데이트
            const selectedDateDisplay = document.getElementById('selected-date-display');
            if (selectedDateDisplay) {
                selectedDateDisplay.textContent = `${year}.${month}.${day} (${dayOfWeek})`;
            }

            // 로딩 표시
            const tbody = document.getElementById('schedule-list-tbody');
            if (tbody) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center" style="padding: 40px;">로딩 중...</td></tr>';
            }

            // AJAX 요청
            if (!schedulesUrl) {
                console.error('schedulesUrl이 설정되지 않았습니다.');
                showError('일정을 불러오는 중 오류가 발생했습니다.');
                return;
            }

            fetch(`${schedulesUrl}?date=${date}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
            })
            .then(function(response) {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(function(data) {
                if (data.success && data.data) {
                    renderSchedules(data.data);
                } else {
                    const errorMsg = data.message || '일정을 불러오는 중 오류가 발생했습니다.';
                    showError(errorMsg);
                }
            })
            .catch(function(error) {
                console.error('Error:', error);
                showError('일정을 불러오는 중 오류가 발생했습니다: ' + error.message);
            });
        }

        function renderSchedules(schedules) {
            const tbody = document.getElementById('schedule-list-tbody');
            if (!tbody) {
                return;
            }

            if (schedules.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center" style="padding: 40px;">등록된 일정이 없습니다.</td></tr>';
                return;
            }

            let html = '';
            schedules.forEach(function(item, index) {
                const content = item.content && item.content.length > 20 
                    ? item.content.substring(0, 20) + '...' 
                    : (item.content || '');
                
                // 프로그램인 경우 추가 정보 표시
                let titleDisplay = item.title;
                if (item.type === 'program') {
                    if (item.is_unlimited_capacity) {
                        titleDisplay += ' <span style="color: #39B54A;">(제한없음)</span>';
                    } else if (item.capacity) {
                        const remaining = item.capacity - (item.applied_count || 0);
                        titleDisplay += ` <span style="color: #39B54A;">(${item.applied_count || 0}/${item.capacity})</span>`;
                    }
                }
                
                html += '<tr>';
                html += `<td>${schedules.length - index}</td>`;
                html += `<td>${titleDisplay}</td>`;
                html += `<td>${item.start_date}</td>`;
                html += `<td>${item.end_date}</td>`;
                html += `<td>${content}</td>`;
                html += '</tr>';
            });

            tbody.innerHTML = html;
        }

        function showError(message) {
            const tbody = document.getElementById('schedule-list-tbody');
            if (tbody) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center" style="padding: 40px; color: #dc3545;">' + message + '</td></tr>';
            }
        }

        function loadScheduleForEdit(scheduleId) {
            fetch(`/backoffice/schedules/${scheduleId}/edit`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const schedule = data.schedule;
                        document.getElementById('schedule-id').value = schedule.id;
                        document.getElementById('title').value = schedule.title;
                        document.getElementById('start_date').value = schedule.start_date;
                        document.getElementById('end_date').value = schedule.end_date;
                        document.getElementById('content').value = schedule.content || '';
                        document.getElementById('disable_application').checked = schedule.disable_application;
                        document.getElementById('form-method').value = 'PUT';
                        scheduleForm.action = `/backoffice/schedules/${scheduleId}`;
                        modalTitle.textContent = '일정수정';
                        showModal();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('일정 정보를 불러오는 중 오류가 발생했습니다.');
                });
        }

        function submitSchedule() {
            const formData = new FormData(scheduleForm);
            const method = document.getElementById('form-method').value;
            const scheduleId = document.getElementById('schedule-id').value;
            let url = storeUrl;
            let options = {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                }
            };

            if (method === 'PUT') {
                url = `/backoffice/schedules/${scheduleId}`;
                formData.append('_method', 'PUT');
            }

            setFormLoading(true);
            fetch(url, options)
                .then(response => {
                if (response.status === 422) {
                    return response.json().then(data => {
                        const errors = data.errors || {};
                        const firstError = Object.values(errors)[0]?.[0] || '입력값을 확인해주세요.';
                        alert(firstError);
                        throw new Error(firstError);
                    });
                }

                    if (response.redirected) {
                        window.location.href = response.url;
                    return null;
                    }

                const contentType = response.headers.get('content-type') || '';
                if (contentType.includes('application/json')) {
                    return response.json();
                }

                return response.text().then(() => {
                    window.location.reload();
                    return null;
                });
                })
                .then(data => {
                    if (data && data.success) {
                        window.location.reload();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('일정 저장 중 오류가 발생했습니다.');
                })
                .finally(() => setFormLoading(false));
        }
    });
})();

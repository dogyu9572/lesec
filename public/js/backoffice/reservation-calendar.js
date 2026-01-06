(function() {
    'use strict';

    document.addEventListener('DOMContentLoaded', function() {
        const configEl = document.getElementById('reservation-calendar-config');
        if (!configEl) {
            return;
        }

        const reservationsUrl = configEl.getAttribute('data-reservations-url');
        const baseUrl = configEl.getAttribute('data-base-url');
        let selectedDate = null;

        // 날짜 클릭 이벤트
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
                loadReservations(date);
            });
        });

        // 예약 내역 로드
        function loadReservations(date) {
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
            const individualTbody = document.getElementById('individual-reservations');
            const groupTbody = document.getElementById('group-reservations');
            
            if (individualTbody) {
                individualTbody.innerHTML = '<tr><td colspan="3" class="text-center" style="padding: 40px;">로딩 중...</td></tr>';
            }
            if (groupTbody) {
                groupTbody.innerHTML = '<tr><td colspan="3" class="text-center" style="padding: 40px;">로딩 중...</td></tr>';
            }

            // AJAX 요청
            fetch(`${reservationsUrl}?date=${date}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
            })
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                if (data.success && data.data) {
                    renderReservations(data.data);
                } else {
                    showError('예약 내역을 불러오는 중 오류가 발생했습니다.');
                }
            })
            .catch(function(error) {
                console.error('Error:', error);
                showError('예약 내역을 불러오는 중 오류가 발생했습니다.');
            });
        }

        // 예약 내역 렌더링
        function renderReservations(data) {
            const individual = data.individual || [];
            const group = data.group || [];

            // 개인 예약 내역
            const individualTbody = document.getElementById('individual-reservations');
            const individualCount = document.getElementById('individual-count');
            
            if (individualTbody) {
                if (individual.length === 0) {
                    individualTbody.innerHTML = '<tr><td colspan="3" class="text-center" style="padding: 40px;">예약 내역이 없습니다.</td></tr>';
                } else {
                    individualTbody.innerHTML = individual.map(function(item) {
                        const capacityText = item.is_unlimited ? '무제한' : (item.capacity ? `${item.applicant_count}/${item.capacity}` : item.applicant_count);
                        let scheduleText = '';
                        
                        // education_start_date와 education_end_date 우선 사용
                        if (item.education_start_date && item.education_end_date) {
                            const startDate = item.education_start_date.split('-').join('.');
                            const endDate = item.education_end_date.split('-').join('.');
                            if (startDate === endDate) {
                                scheduleText = startDate;
                            } else {
                                scheduleText = `${startDate} ~ ${endDate}`;
                            }
                        } else if (item.education_start_date) {
                            // 시작일만 있는 경우
                            scheduleText = item.education_start_date.split('-').join('.');
                        } else if (item.date) {
                            // date 필드 사용 (fallback)
                            scheduleText = item.date.split('-').join('.');
                        }
                        
                        const editUrl = `/backoffice/rosters/${item.program_reservation_id}/edit`;
                        
                        return `
                            <tr style="cursor: pointer;" onclick="window.location.href='${editUrl}'">
                                <td>${scheduleText}</td>
                                <td>${escapeHtml(item.program_name)}</td>
                                <td>${capacityText}</td>
                            </tr>
                        `;
                    }).join('');
                }
            }

            if (individualCount) {
                if (individual.length > 0) {
                    individualCount.textContent = individual.length;
                    individualCount.style.display = 'inline-block';
                } else {
                    individualCount.style.display = 'none';
                }
            }

            // 단체 예약 내역
            const groupTbody = document.getElementById('group-reservations');
            const groupCount = document.getElementById('group-count');
            
            if (groupTbody) {
                if (group.length === 0) {
                    groupTbody.innerHTML = '<tr><td colspan="3" class="text-center" style="padding: 40px;">예약 내역이 없습니다.</td></tr>';
                } else {
                    groupTbody.innerHTML = group.map(function(item) {
                        const capacityText = item.is_unlimited ? '무제한' : (item.capacity ? `${item.applicant_count}/${item.capacity}` : item.applicant_count);
                        let scheduleText = '';
                        
                        // education_start_date와 education_end_date 우선 사용
                        if (item.education_start_date && item.education_end_date) {
                            const startDate = item.education_start_date.split('-').join('.');
                            const endDate = item.education_end_date.split('-').join('.');
                            if (startDate === endDate) {
                                scheduleText = startDate;
                            } else {
                                scheduleText = `${startDate} ~ ${endDate}`;
                            }
                        } else if (item.education_start_date) {
                            // 시작일만 있는 경우
                            scheduleText = item.education_start_date.split('-').join('.');
                        } else if (item.date) {
                            // date 필드 사용 (fallback)
                            scheduleText = item.date.split('-').join('.');
                        }
                        
                        const editUrl = `/backoffice/rosters/${item.program_reservation_id}/edit`;
                        
                        return `
                            <tr style="cursor: pointer;" onclick="window.location.href='${editUrl}'">
                                <td>${scheduleText}</td>
                                <td>${escapeHtml(item.program_name)}</td>
                                <td>${capacityText}</td>
                            </tr>
                        `;
                    }).join('');
                }
            }

            if (groupCount) {
                if (group.length > 0) {
                    groupCount.textContent = group.length;
                    groupCount.style.display = 'inline-block';
                } else {
                    groupCount.style.display = 'none';
                }
            }
        }

        // 에러 표시
        function showError(message) {
            const individualTbody = document.getElementById('individual-reservations');
            const groupTbody = document.getElementById('group-reservations');
            
            if (individualTbody) {
                individualTbody.innerHTML = `<tr><td colspan="3" class="text-center" style="padding: 40px; color: red;">${escapeHtml(message)}</td></tr>`;
            }
            if (groupTbody) {
                groupTbody.innerHTML = `<tr><td colspan="3" class="text-center" style="padding: 40px; color: red;">${escapeHtml(message)}</td></tr>`;
            }
        }

        // HTML 이스케이프
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // 월 네비게이션
        document.querySelectorAll('.arrow.prev, .arrow.next').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const currentYear = parseInt(this.getAttribute('data-year'));
                const currentMonth = parseInt(this.getAttribute('data-month'));
                
                let newYear = currentYear;
                let newMonth = currentMonth;

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

        // 당일보기 버튼
        const todayBtn = document.querySelector('.btn_today');
        if (todayBtn) {
            todayBtn.addEventListener('click', function() {
                const today = new Date();
                const year = today.getFullYear();
                const month = today.getMonth() + 1;
                const day = String(today.getDate()).padStart(2, '0');
                const dateStr = `${year}-${String(month).padStart(2, '0')}-${day}`;
                window.location.href = `${baseUrl}?year=${year}&month=${month}&date=${dateStr}`;
            });
        }

        // 페이지 로드 시 선택된 날짜가 있으면 자동으로 활성화
        const urlParams = new URLSearchParams(window.location.search);
        const selectedDateParam = urlParams.get('date');
        if (selectedDateParam) {
            const dayEl = document.querySelector(`.calendar-day[data-date="${selectedDateParam}"]`);
            if (dayEl && dayEl.classList.contains('clickable')) {
                // 선택된 날짜 스타일 업데이트
                document.querySelectorAll('.calendar-day').forEach(function(el) {
                    el.classList.remove('selected');
                });
                dayEl.classList.add('selected');
                selectedDate = selectedDateParam;
                loadReservations(selectedDateParam);
            }
        }
    });
})();


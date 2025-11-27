document.addEventListener('DOMContentLoaded', function() {
    // 연도 필터 엔터키 처리
    document.getElementById('year-filter')?.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            loadYearStats();
        }
    });
});

/**
 * 연별 통계 조회
 */
function loadYearStats() {
    const year = document.getElementById('year-filter').value;
    if (!year) {
        alert('연도를 입력하세요.');
        return;
    }

    fetchStatistics('year', year, null, null, null, null, function(data) {
        updateYearStatsTable(data);
    });
}

/**
 * 월별 통계 조회
 */
function loadMonthStats() {
    const startMonth = document.getElementById('month-start-filter').value;
    const endMonth = document.getElementById('month-end-filter').value;
    
    if (!startMonth || !endMonth) {
        alert('시작월과 종료월을 선택하세요.');
        return;
    }

    // 최대 12개월 제한 검증
    const start = new Date(startMonth + '-01');
    const end = new Date(endMonth + '-01');
    const monthsDiff = (end.getFullYear() - start.getFullYear()) * 12 + (end.getMonth() - start.getMonth()) + 1;
    
    if (monthsDiff > 12) {
        alert('조회 기간은 최대 12개월까지 가능합니다.');
        return;
    }

    fetchStatistics('month', null, null, null, startMonth, endMonth, function(data) {
        updateMonthStatsTable(data);
    });
}

/**
 * 날짜별 통계 조회
 */
function loadDateStats() {
    const startDate = document.getElementById('date-start-filter').value;
    const endDate = document.getElementById('date-end-filter').value;
    
    if (!startDate || !endDate) {
        alert('시작일과 종료일을 선택하세요.');
        return;
    }

    // 최대 30일 제한 검증
    const start = new Date(startDate);
    const end = new Date(endDate);
    const daysDiff = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1;
    
    if (daysDiff > 30) {
        alert('조회 기간은 최대 30일까지 가능합니다.');
        return;
    }

    fetchStatistics('date', null, startDate, endDate, null, null, function(data) {
        updateDateStatsTable(data);
    });
}

/**
 * 시간별 통계 조회
 */
function loadHourStats() {
    const date = document.getElementById('hour-date-filter').value;
    if (!date) {
        alert('날짜를 선택하세요.');
        return;
    }

    fetchStatistics('hour', date, null, null, null, null, function(data) {
        updateHourStatsTable(data);
    });
}

/**
 * AJAX 통계 조회
 */
function fetchStatistics(type, date, startDate, endDate, startMonth, endMonth, callback) {
    const url = '/backoffice/access-statistics/get-statistics';
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    let queryParams = 'type=' + type;
    if (date) {
        queryParams += '&date=' + date;
    }
    if (startDate) {
        queryParams += '&start_date=' + startDate;
    }
    if (endDate) {
        queryParams += '&end_date=' + endDate;
    }
    if (startMonth) {
        queryParams += '&start_month=' + startMonth;
    }
    if (endMonth) {
        queryParams += '&end_month=' + endMonth;
    }

    fetch(url + '?' + queryParams, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (callback) {
            callback(data);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('통계 조회 중 오류가 발생했습니다.');
    });
}

/**
 * 연별 통계 테이블 업데이트
 */
function updateYearStatsTable(stats) {
    const tableContainer = document.getElementById('year-stats-table');
    if (!tableContainer || !stats || stats.length === 0) {
        return;
    }

    let html = '<table class="stats-table">';
    html += '<thead><tr><th>연도</th>';
    stats.forEach(function(stat) {
        html += '<th>' + stat.label + '</th>';
    });
    html += '</tr></thead>';
    html += '<tbody><tr><td>방문자수</td>';
    stats.forEach(function(stat) {
        html += '<td>' + formatNumber(stat.count) + '명</td>';
    });
    html += '</tr></tbody></table>';

    tableContainer.innerHTML = html;
}

/**
 * 월별 통계 테이블 업데이트
 */
function updateMonthStatsTable(stats) {
    const tableContainer = document.getElementById('month-stats-table');
    if (!tableContainer || !stats || stats.length === 0) {
        return;
    }

    let html = '<table class="stats-table">';
    html += '<thead><tr><th>월</th>';
    stats.forEach(function(stat) {
        html += '<th>' + stat.label + '</th>';
    });
    html += '</tr></thead>';
    html += '<tbody><tr><td>방문자수</td>';
    stats.forEach(function(stat) {
        html += '<td>' + formatNumber(stat.count) + '명</td>';
    });
    html += '</tr></tbody></table>';

    tableContainer.innerHTML = html;
}

/**
 * 날짜별 통계 테이블 업데이트
 */
function updateDateStatsTable(stats) {
    const tableContainer = document.getElementById('date-stats-table');
    if (!tableContainer || !stats || stats.length === 0) {
        return;
    }

    let html = '<table class="stats-table">';
    html += '<thead><tr><th>날짜</th>';
    stats.forEach(function(stat) {
        html += '<th>' + stat.label + '</th>';
    });
    html += '</tr></thead>';
    html += '<tbody><tr><td>방문자수</td>';
    stats.forEach(function(stat) {
        html += '<td>' + formatNumber(stat.count) + '명</td>';
    });
    html += '</tr></tbody></table>';

    tableContainer.innerHTML = html;
}

/**
 * 시간별 통계 테이블 업데이트
 */
function updateHourStatsTable(stats) {
    const tableContainer = document.getElementById('hour-stats-table');
    if (!tableContainer || !stats || stats.length === 0) {
        return;
    }

    let html = '<table class="stats-table">';
    html += '<thead><tr><th>시간</th>';
    stats.forEach(function(stat) {
        html += '<th>' + stat.label + '</th>';
    });
    html += '</tr></thead>';
    html += '<tbody><tr><td>방문자수</td>';
    stats.forEach(function(stat) {
        html += '<td>' + formatNumber(stat.count) + '명</td>';
    });
    html += '</tr></tbody></table>';

    tableContainer.innerHTML = html;
}

/**
 * 숫자 포맷팅
 */
function formatNumber(num) {
    return new Intl.NumberFormat('ko-KR').format(num);
}


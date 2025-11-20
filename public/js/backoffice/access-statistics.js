document.addEventListener('DOMContentLoaded', function() {
    // 연도 필터 엔터키 처리
    document.getElementById('year-filter')?.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            loadYearStats();
        }
    });

    // 월별 연도 필터 엔터키 처리
    document.getElementById('month-year-filter')?.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            loadMonthStats();
        }
    });

    // 날짜별 연월 필터 변경 처리
    document.getElementById('date-month-filter')?.addEventListener('change', function() {
        loadDateStats();
    });

    // 시간별 날짜 필터 변경 처리
    document.getElementById('hour-date-filter')?.addEventListener('change', function() {
        loadHourStats();
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

    fetchStatistics('year', year, function(data) {
        updateYearStatsTable(data);
    });
}

/**
 * 월별 통계 조회
 */
function loadMonthStats() {
    const year = document.getElementById('month-year-filter').value;
    if (!year) {
        alert('연도를 입력하세요.');
        return;
    }

    fetchStatistics('month', year, function(data) {
        updateMonthStatsTable(data);
    });
}

/**
 * 날짜별 통계 조회
 */
function loadDateStats() {
    const month = document.getElementById('date-month-filter').value;
    if (!month) {
        alert('연월을 선택하세요.');
        return;
    }

    fetchStatistics('date', month, function(data) {
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

    fetchStatistics('hour', date, function(data) {
        updateHourStatsTable(data);
    });
}

/**
 * AJAX 통계 조회
 */
function fetchStatistics(type, date, callback) {
    const url = '/backoffice/access-statistics/get-statistics';
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    fetch(url + '?type=' + type + '&date=' + date, {
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


/* 프로그램 전용 스크립트 */
(function ($) {
    'use strict';

    function getData($el, key, defaultValue) {
        if (!$el || !$el.length) {
            return defaultValue;
        }

        const value = $el.data(key);
        return typeof value === 'undefined' ? defaultValue : value;
    }

    function navigateTo(url) {
        if (!url) {
            return;
        }
        window.location.href = url;
    }

    function initApplyPages() {
        const $wraps = $('.apply_write[data-program-page="apply"]');
        if (!$wraps.length) {
            return;
        }

        const $header = $('.header');

        $wraps.each(function () {
            const $wrap = $(this);
            const $abso = $wrap.find('.absoarea');
            const $start = $wrap.find('#start');
            const $end = $wrap.find('#end');

            if (!$start.length || !$end.length) {
                return;
            }

            function updateBottomOffset() {
                const bottomValue = Math.max(0, ($abso.outerHeight() || 0) + ($header.outerHeight() || 0));
                $end.css('bottom', `${bottomValue}px`);
            }

            function handleScroll() {
                const scrollTop = $(window).scrollTop();
                const startTop = $start.offset().top;
                const endTop = $end.offset().top;

                if (scrollTop >= endTop) {
                    $wrap.addClass('end').removeClass('start');
                } else if (scrollTop >= startTop) {
                    $wrap.addClass('start').removeClass('end');
                } else {
                    $wrap.removeClass('start end');
                }
            }

            $(window)
                .on('scroll.programApply', handleScroll)
                .on('resize.programApply', updateBottomOffset);

            updateBottomOffset();
            handleScroll();
        });

        $(document).on('click', '[data-navigate-select]', function () {
            const url = $(this).data('selectUrl');
            navigateTo(url);
        });
    }

    function updateMonthDisplay($wrap, currentDate) {
        const year = currentDate.getFullYear();
        const month = (currentDate.getMonth() + 1).toString().padStart(2, '0');
        $wrap.find('.schedule_top .month strong').text(`${year}.${month}`);
    }

    function selectTodayIfVisible($wrap, currentDate, daysKor, updateSelectionInfo) {
        const today = new Date();

        if (
            currentDate.getFullYear() === today.getFullYear() &&
            currentDate.getMonth() === today.getMonth()
        ) {
            $wrap.find('.schedule_table .table tbody td').each(function () {
                const $cell = $(this);
                const dayText = $cell.find('span').text();
                if ($cell.hasClass('disabled')) {
                    return;
                }

                if (parseInt(dayText, 10) === today.getDate()) {
                    $wrap.find('.schedule_table .table tbody td').removeClass('select');
                    $cell.addClass('select');
                    updateSelectionInfo($cell);
                }
            });
        }
    }

    function initGroupSelect($wrap) {
        if (!$wrap.length) {
            return;
        }

        const currentYear = parseInt(getData($wrap, 'year', new Date().getFullYear()), 10);
        const currentMonth = parseInt(getData($wrap, 'month', new Date().getMonth() + 1), 10);
        const baseUrl = getData($wrap, 'baseUrl', '');
        const applyUrl = getData($wrap, 'applyUrl', '');
        const daysKor = ['일', '월', '화', '수', '목', '금', '토'];
        let currentDate = new Date(currentYear, currentMonth - 1, 1);
        let selectedProgramData = null;
        const $approvalModal = $('#pop_approval');

        function updateSelectionInfo($td) {
            const day = $td.find('span').text().padStart(2, '0');
            const year = currentDate.getFullYear();
            const month = (currentDate.getMonth() + 1).toString().padStart(2, '0');
            const selectedDate = `${year}.${month}.${day}`;
            const dateObj = new Date(`${year}-${month}-${day}`);
            const dayOfWeek = daysKor[dateObj.getDay()];

            $wrap.find('.glbox.select_day .day dd').html(`${selectedDate} <span>(${dayOfWeek})</span>`);

            const $tbody = $wrap.find('.glbox.select_day .tbl tbody');
            $tbody.empty();
            selectedProgramData = null;

            $td.find('.list li').each(function () {
                const $item = $(this);
                const title = $item.text();
                const programId = $item.data('programId') || 0;
                const programName = $item.data('programName') || title;
                const educationDate = $item.data('educationDate') || '';
                const educationType = $item.data('educationType') || '';
                const educationFee = $item.data('educationFee') || 0;
                const applied = $item.data('applied') || 0;
                const total = $item.data('total') || 24;
                const remain = total - applied;
                const remainText = `${applied}/${total}`;

                let status = '';
                let stateClass = '';

                if (remain <= 0) {
                    status = '마감';
                    stateClass = 'c3';
                } else if (applied > 0) {
                    status = '잔여석 신청 가능';
                    stateClass = 'c2';
                } else {
                    status = '신청가능';
                    stateClass = 'c1';
                }

                $tbody.append(
                    `<tr data-program-id="${programId}" 
                         data-program-name="${programName}"
                         data-education-date="${educationDate}"
                         data-education-type="${educationType}"
                         data-education-fee="${educationFee}">
                        <td class="edu11"><label class="check solo"><input type="radio" name="select_day" data-program-id="${programId}"><i></i></label></td>
                        <td class="edu12">${year}-${month}-${day} 09:00</td>
                        <td class="edu13 over_dot">${title}</td>
                        <td class="edu14">${remainText}</td>
                        <td class="edu15"><i class="state ${stateClass}">${status}</i></td>
                    </tr>`
                );
            });
        }

        function adjustScrollHeight() {
            const $scheduleTable = $wrap.find('.schedule_table');
            const $nebox = $wrap.find('.nebox');
            const scheduleHeight = $scheduleTable.outerHeight(true) - ($nebox.outerHeight(true) || 0);

            if (window.innerWidth >= 1200) {
                $wrap.find('.select_day').css({ height: scheduleHeight, 'max-height': '' });
            }

            const $btm = $wrap.find('.select_day .btm');
            const $day = $wrap.find('.select_day .day');
            const $top = $wrap.find('.select_day .top');
            const padding = (parseInt($top.css('padding-top'), 10) || 0) + (parseInt($top.css('padding-bottom'), 10) || 0);
            const scrollHeight = scheduleHeight - ($btm.outerHeight(true) || 0) - ($day.outerHeight(true) || 0) - padding;

            if (window.innerWidth >= 1200) {
                $wrap.find('.select_day .scroll').css({ height: scrollHeight, 'max-height': '' });
            }
        }

        updateMonthDisplay($wrap, currentDate);
        selectTodayIfVisible($wrap, currentDate, daysKor, updateSelectionInfo);
        adjustScrollHeight();

        $(window).on('resize.programSelectGroup', adjustScrollHeight);

        $wrap.find('.arrow.prev').on('click', function () {
            currentDate.setMonth(currentDate.getMonth() - 1);
            const year = currentDate.getFullYear();
            const month = currentDate.getMonth() + 1;
            navigateTo(`${baseUrl}?year=${year}&month=${month}`);
        });

        $wrap.find('.arrow.next').on('click', function () {
            currentDate.setMonth(currentDate.getMonth() + 1);
            const year = currentDate.getFullYear();
            const month = currentDate.getMonth() + 1;
            navigateTo(`${baseUrl}?year=${year}&month=${month}`);
        });

        $wrap.find('.btn_today').on('click', function () {
            $wrap.find('.schedule_table .table tbody td').removeClass('select');
            selectTodayIfVisible($wrap, currentDate, daysKor, updateSelectionInfo);
        });

        $wrap.find('.schedule_table .table tbody td').on('click', function () {
            const $cell = $(this);
            if ($cell.hasClass('disabled')) {
                return;
            }
            $wrap.find('.schedule_table .table tbody td').removeClass('select');
            $cell.addClass('select');
            updateSelectionInfo($cell);
        });

        $wrap.find('.btn.plus').on('click', function () {
            const $input = $wrap.find('.count input');
            const value = parseInt($input.val(), 10);
            if (value === 0) {
                $input.val(10);
            }
        });

        $wrap.find('.btn.minus').on('click', function () {
            const $input = $wrap.find('.count input');
            const value = parseInt($input.val(), 10);
            if (value === 10) {
                $input.val(0);
            }
        });

        $wrap.find('.glbox.select_day .tbl').on('change', 'input[type="radio"]', function () {
            $wrap.find('.count input').val(10);
            const $row = $(this).closest('tr');
            selectedProgramData = {
                programId: $row.data('programId'),
                programName: $row.data('programName'),
                educationDate: $row.data('educationDate'),
                educationType: $row.data('educationType'),
                educationFee: $row.data('educationFee')
            };
        });

        $wrap.find('[data-layer-open="pop_approval"]').on('click', function (event) {
            event.preventDefault();
            event.stopImmediatePropagation();

            if (!selectedProgramData || !selectedProgramData.programId) {
                alert('프로그램을 선택해주세요.');
                return;
            }

            $approvalModal.fadeIn(300);
        });

        $('#group-submit-btn').on('click', function (event) {
            event.preventDefault();
            event.stopImmediatePropagation();

            const $agreementCheckbox = $('#agreement_checkbox');
            
            if (!$agreementCheckbox.is(':checked')) {
                alert('승인 안내 내용에 동의해주세요.');
                return;
            }

            if (!selectedProgramData || !selectedProgramData.programId) {
                alert('프로그램을 선택해주세요.');
                return;
            }

            const applicantCount = parseInt($wrap.find('.count input').val(), 10) || 10;

            if (applicantCount < 4) {
                alert('단체 신청은 최소 4명 이상이어야 합니다.');
                return;
            }

            const csrfToken = $('meta[name="csrf-token"]').attr('content');
            
            if (!csrfToken) {
                alert('CSRF 토큰을 찾을 수 없습니다. 페이지를 새로고침해주세요.');
                return;
            }

            if (!applyUrl) {
                alert('신청 URL이 설정되지 않았습니다.');
                return;
            }

            $.ajax({
                url: applyUrl,
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                data: {
                    program_reservation_id: selectedProgramData.programId,
                    applicant_count: applicantCount,
                    agreement: 1
                },
                success: function (response) {
                    if (response.success && response.redirect_url) {
                        window.location.href = response.redirect_url;
                    } else {
                        alert(response.message || '신청이 완료되었습니다.');
                        if (response.redirect_url) {
                            window.location.href = response.redirect_url;
                        }
                    }
                },
                error: function (xhr) {
                    let errorMessage = '신청 중 오류가 발생했습니다.';
                    
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.status === 401) {
                        errorMessage = '로그인이 필요합니다.';
                    } else if (xhr.status === 400) {
                        errorMessage = '잘못된 요청입니다.';
                    }
                    
                    alert(errorMessage);
                }
            });
        });
    }

    function initIndividualSelect($container) {
        if (!$container.length) {
            return;
        }

        const $form = $container.find('#individual-filter-form');

        if ($form.length) {
            $form.find('select').on('change', function () {
                $form.trigger('submit');
            });
        }
    }

    function initSelectPages() {
        const $selectWraps = $('[data-program-page="select"]');
        if (!$selectWraps.length) {
            return;
        }

        $selectWraps.each(function () {
            const $wrap = $(this);
            const mode = getData($wrap, 'selectMode', '');

            if (mode === 'group') {
                initGroupSelect($wrap);
            }

            if (mode === 'individual') {
                initIndividualSelect($wrap);
            }
        });
    }

    function initLayerHandlers() {
        $(document).on('click', '[data-layer-open]', function () {
            const targetId = $(this).data('layerOpen');
            if (!targetId) {
                return;
            }
            $(`#${targetId}`).fadeIn(300);
        });

        $(document).on('click', '[data-layer-close]', function () {
            const targetId = $(this).data('layerClose');
            if (!targetId) {
                return;
            }
            $(`#${targetId}`).fadeOut(300);
        });
    }

    function initCompletionNavigation() {
        $(document).on('click', '[data-navigate-complete]', function () {
            const url = $(this).data('navigateComplete');
            navigateTo(url);
        });
    }

    $(function () {
        initLayerHandlers();
        initCompletionNavigation();
        initApplyPages();
        initSelectPages();
    });
})(jQuery);



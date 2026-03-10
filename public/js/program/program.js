/* 프로그램 전용 스크립트 */
(function ($) {
  "use strict";

  function getData($el, key, defaultValue) {
    if (!$el || !$el.length) {
      return defaultValue;
    }

    const value = $el.data(key);
    return typeof value === "undefined" ? defaultValue : value;
  }

  function navigateTo(url) {
    if (!url) {
      return;
    }
    window.location.href = url;
  }

  function saveScrollPosition(key) {
    if (!key) {
      return;
    }
    try {
      sessionStorage.setItem(
        key,
        JSON.stringify({
          y: window.scrollY || 0,
          path: window.location.pathname,
          // 너무 오래된 값 복원 방지 (10초)
          expiresAt: Date.now() + 10000,
        })
      );
    } catch (e) {
      // sessionStorage 사용 불가 시 무시
    }
  }

  function restoreScrollPosition(key) {
    if (!key) {
      return;
    }
    try {
      const raw = sessionStorage.getItem(key);
      if (!raw) {
        return;
      }
      sessionStorage.removeItem(key);

      const data = JSON.parse(raw);
      if (!data || typeof data.y !== "number") {
        return;
      }
      if (data.path !== window.location.pathname) {
        return;
      }
      if (data.expiresAt && Date.now() > data.expiresAt) {
        return;
      }

      // 렌더링 후 복원
      setTimeout(function () {
        window.scrollTo(0, data.y);
      }, 0);
    } catch (e) {
      // sessionStorage 사용 불가 시 무시
    }
  }

  function initApplyPages() {
    const $wraps = $('.apply_write[data-program-page="apply"]');
    if (!$wraps.length) {
      return;
    }

    const $header = $(".header");

    $wraps.each(function () {
      const $wrap = $(this);
      const $abso = $wrap.find(".absoarea");
      const $start = $wrap.find("#start");
      const $end = $wrap.find("#end");

      if (!$start.length || !$end.length) {
        return;
      }

      function updateBottomOffset() {
        const bottomValue = Math.max(
          0,
          ($abso.outerHeight() || 0) + ($header.outerHeight() || 0)
        );
        $end.css("bottom", `${bottomValue}px`);
      }

      function handleScroll() {
        const scrollTop = $(window).scrollTop();
        const startTop = $start.offset().top;
        const endTop = $end.offset().top;

        if (scrollTop >= endTop) {
          $wrap.addClass("end").removeClass("start");
        } else if (scrollTop >= startTop) {
          $wrap.addClass("start").removeClass("end");
        } else {
          $wrap.removeClass("start end");
        }
      }

      $(window)
        .on("scroll.programApply", handleScroll)
        .on("resize.programApply", updateBottomOffset);

      updateBottomOffset();
      handleScroll();
    });

    $(document).on("click", "[data-navigate-select]", function (e) {
      e.preventDefault();
      const $button = $(this);
      const url = $button.data("selectUrl");
      const mode = $button.data("navigateSelect");
      const $wrap = $button.closest("[data-apply-mode]");
      const memberType = $wrap.length ? $wrap.data("memberType") : null;

      // 단체 신청 체크: 학생은 단체 신청 불가
      if (mode === "group" && memberType === "student") {
        alert("단체 신청은 교사만 가능합니다. 개인 신청을 이용해주세요.");
        window.history.back();
        return;
      }

      // 개인 신청 체크: 교사는 개인 신청 불가
      if (mode === "individual" && memberType === "teacher") {
        alert("개인 신청은 학생만 가능합니다. 단체 신청을 이용해주세요.");
        window.history.back();
        return;
      }

      navigateTo(url);
    });
  }

  function updateMonthDisplay($wrap, currentDate) {
    const year = currentDate.getFullYear();
    const month = (currentDate.getMonth() + 1).toString().padStart(2, "0");
    $wrap.find(".schedule_top .month strong").text(`${year}.${month}`);
  }

  function selectTodayIfVisible(
    $wrap,
    currentDate,
    daysKor,
    updateSelectionInfo
  ) {
    const today = new Date();

    if (
      currentDate.getFullYear() === today.getFullYear() &&
      currentDate.getMonth() === today.getMonth()
    ) {
      $wrap.find(".schedule_table .table tbody td").each(function () {
        const $cell = $(this);
        const dayText = $cell.find("span").text();
        if ($cell.hasClass("disabled")) {
          return;
        }

        if (parseInt(dayText, 10) === today.getDate()) {
          $wrap.find(".schedule_table .table tbody td").removeClass("select");
          $cell.addClass("select");
          updateSelectionInfo($cell);
        }
      });
    }
  }

  function initGroupSelect($wrap) {
    if (!$wrap.length) {
      return;
    }

    const scrollKey = "program_group_select_scroll_" + window.location.pathname;

    const currentYear = parseInt(
      getData($wrap, "year", new Date().getFullYear()),
      10
    );
    const currentMonth = parseInt(
      getData($wrap, "month", new Date().getMonth() + 1),
      10
    );
    const baseUrl = getData($wrap, "baseUrl", "");
    const applyUrl = getData($wrap, "applyUrl", "");
    const daysKor = ["일", "월", "화", "수", "목", "금", "토"];
    let currentDate = new Date(currentYear, currentMonth - 1, 1);
    let selectedProgramData = null;
    const $approvalModal = $("#pop_approval");

    function updateSelectionInfo($td) {
      // td의 첫 번째 span에서 날짜 가져오기 (프로그램 리스트의 span과 구분)
      const dayText = $td.children("span").first().text().trim();
      const day = parseInt(dayText, 10);

      // 유효한 날짜인지 확인
      if (isNaN(day) || day < 1 || day > 31) {
        console.error("Invalid day:", dayText);
        return;
      }

      const year = currentDate.getFullYear();
      const month = (currentDate.getMonth() + 1).toString().padStart(2, "0");
      const dayStr = day.toString().padStart(2, "0");
      const selectedDate = `${year}.${month}.${dayStr}`;

      // 날짜 객체 생성 및 유효성 검증
      const dateObj = new Date(year, currentDate.getMonth(), day);
      if (isNaN(dateObj.getTime())) {
        console.error("Invalid date:", year, month, day);
        return;
      }

      const dayOfWeek = daysKor[dateObj.getDay()] || "일";

      $wrap
        .find(".glbox.select_day .day dd")
        .html(`${selectedDate} <span>(${dayOfWeek})</span>`);

      const $tbody = $wrap.find(".glbox.select_day .tbl tbody");
      $tbody.empty();
      selectedProgramData = null;
      $wrap.find(".count input").val(0);

      $td.find(".list li").each(function () {
        const $item = $(this);
        const title = $item.text();
        const programId = $item.data("programId") || 0;
        const programName = $item.data("programName") || title;
        const educationStartDate = $item.data("educationStartDate") || "";
        const educationEndDate = $item.data("educationEndDate") || "";
        const applicationEndDate = $item.data("applicationEndDate") || "";
        const educationType = $item.data("educationType") || "";
        const educationFee = $item.data("educationFee") || 0;
        const applied = $item.data("applied") || 0;
        const isUnlimited =
          $item.data("isUnlimited") === 1 ||
          $item.data("is-unlimited") === 1 ||
          $item.data("isUnlimited") === "1" ||
          $item.data("is-unlimited") === "1";
        const total = $item.data("total") || 0;
        const remain = isUnlimited ? 9999 : total - applied;
        const remainText = isUnlimited
          ? `${applied}/제한없음`
          : `${applied}/${total}`;

        // 접수기간 종료 체크
        const now = new Date();
        let isApplicationClosed = false;

        if (applicationEndDate) {
          const endDate = new Date(applicationEndDate);
          endDate.setHours(23, 59, 59, 999); // 종료일 마지막 시간까지
          if (now > endDate) {
            isApplicationClosed = true;
          }
        }

        let status = "";
        let stateClass = "";

        if (isApplicationClosed) {
          status = "마감";
          stateClass = "c3";
        } else if (remain <= 0) {
          status = "마감";
          stateClass = "c3";
        } else if (applied > 0) {
          status = "잔여석 신청 가능";
          stateClass = "c2";
        } else {
          status = "신청가능";
          stateClass = "c1";
        }

        // 교육기간 포맷팅 (시작일 ~ 종료일)
        let educationPeriod = "";
        if (educationStartDate) {
          const startDate = new Date(educationStartDate);
          const startFormatted = `${startDate.getFullYear()}.${String(
            startDate.getMonth() + 1
          ).padStart(2, "0")}.${String(startDate.getDate()).padStart(2, "0")}`;

          if (educationEndDate && educationStartDate !== educationEndDate) {
            const endDate = new Date(educationEndDate);
            const endFormatted = `${endDate.getFullYear()}.${String(
              endDate.getMonth() + 1
            ).padStart(2, "0")}.${String(endDate.getDate()).padStart(2, "0")}`;
            educationPeriod = `${startFormatted} ~ ${endFormatted}`;
          } else {
            educationPeriod = startFormatted;
          }
        } else {
          // 교육기간이 없으면 선택한 날짜 표시
          educationPeriod = `${year}-${month}-${day}`;
        }

        const isVacation =
          educationType === "middle_vacation" ||
          educationType === "high_vacation";
        $tbody.append(
          `<tr data-program-id="${programId}" 
                         data-program-name="${programName}"
                         data-education-start-date="${educationStartDate}"
                         data-education-end-date="${educationEndDate}"
                         data-education-type="${educationType}"
                         data-education-fee="${educationFee}"
                         data-status="${status}"
                         data-applied="${applied}"
                         data-total="${total}"
                         data-is-unlimited="${isUnlimited ? "1" : "0"}"
                         data-is-vacation="${isVacation ? "1" : "0"}">
                        <td class="edu11"><label class="check solo"><input type="radio" name="select_day" data-program-id="${programId}"><i></i></label></td>
						<td class="edu12">${educationPeriod}</td>
                        <td class="edu13 over_dot">${title}</td>
                        <td class="edu14">${remainText}</td>
                        <td class="edu15"><i class="state ${stateClass}">${status}</i></td>
                    </tr>`
        );
      });

      // 프로그램 목록 업데이트 후 + 버튼 상태 업데이트
      updatePlusButtonState();

      // 첫 번째 프로그램이 선택되어 있으면 안내 문구 업데이트
      const $firstRow = $tbody.find("tr").first();
      if ($firstRow.length) {
        const firstProgramInfo = {
          isVacation:
            $firstRow.data("isVacation") === 1 ||
            $firstRow.data("is-vacation") === 1 ||
            $firstRow.data("isVacation") === "1" ||
            $firstRow.data("is-vacation") === "1",
          isUnlimited:
            $firstRow.data("isUnlimited") === 1 ||
            $firstRow.data("is-unlimited") === 1 ||
            $firstRow.data("isUnlimited") === "1" ||
            $firstRow.data("is-unlimited") === "1",
          total: parseInt($firstRow.data("total"), 10) || 0,
        };
        updateVacationInfo(firstProgramInfo);
      }
    }

    // 방학 프로그램 안내 문구 업데이트
    function updateVacationInfo(programInfo) {
      const $info = $wrap.find("#group-application-info");
      if (!$info.length) return;

      if (
        programInfo &&
        programInfo.isVacation &&
        !programInfo.isUnlimited &&
        programInfo.total > 0
      ) {
        // 신청가능 상태면 정원을 모두 채워야 하고, 잔여석 신청 가능 상태면 잔여석을 모두 채워야 함
        if (programInfo.status === "신청가능") {
          $info.text(
            `방학 프로그램은 정원(${programInfo.total}명)을 모두 채워야 신청 가능합니다.`
          );
        } else if (programInfo.status === "잔여석 신청 가능") {
          $info.text(
            `방학 프로그램은 잔여석(${programInfo.remaining}명)을 모두 채워야 신청 가능합니다.`
          );
        } else {
          $info.text(
            `방학 프로그램은 정원(${programInfo.total}명)을 모두 채워야 신청 가능합니다.`
          );
        }
      } else {
        $info.text("단체 신청 가능 인원은 최소 10명입니다.");
      }
    }

    function adjustScrollHeight() {
      const $scheduleTable = $wrap.find(".schedule_table");
      const $nebox = $wrap.find(".nebox");
      const scheduleHeight =
        $scheduleTable.outerHeight(true) - ($nebox.outerHeight(true) || 0);

      /*if (window.innerWidth >= 1200) {
                $wrap.find('.select_day').css({ height: scheduleHeight, 'max-height': '' });
            }*/

      const $btm = $wrap.find(".select_day .btm");
      const $day = $wrap.find(".select_day .day");
      const $top = $wrap.find(".select_day .top");
      const padding =
        (parseInt($top.css("padding-top"), 10) || 0) +
        (parseInt($top.css("padding-bottom"), 10) || 0);
      const scrollHeight =
        scheduleHeight -
        ($btm.outerHeight(true) || 0) -
        ($day.outerHeight(true) || 0) -
        padding;

      /*if (window.innerWidth >= 1200) {
                $wrap.find('.select_day .scroll').css({ height: scrollHeight, 'max-height': '' });
            }*/
    }

    updateMonthDisplay($wrap, currentDate);
    selectTodayIfVisible($wrap, currentDate, daysKor, updateSelectionInfo);
    adjustScrollHeight();
    restoreScrollPosition(scrollKey);

    $(window).on("resize.programSelectGroup", adjustScrollHeight);

    $wrap.find(".arrow.prev").on("click", function () {
      saveScrollPosition(scrollKey);
      currentDate.setMonth(currentDate.getMonth() - 1);
      const year = currentDate.getFullYear();
      const month = currentDate.getMonth() + 1;
      navigateTo(`${baseUrl}?year=${year}&month=${month}`);
    });

    $wrap.find(".arrow.next").on("click", function () {
      saveScrollPosition(scrollKey);
      currentDate.setMonth(currentDate.getMonth() + 1);
      const year = currentDate.getFullYear();
      const month = currentDate.getMonth() + 1;
      navigateTo(`${baseUrl}?year=${year}&month=${month}`);
    });

    /*$wrap.find('.btn_today').on('click', function () {
            $wrap.find('.schedule_table .table tbody td').removeClass('select');
            selectTodayIfVisible($wrap, currentDate, daysKor, updateSelectionInfo);
        });*/

    $wrap.find(".btn_today").on("click", function () {
      const today = new Date();
      const todayYear = today.getFullYear();
      const todayMonth = today.getMonth() + 1;

      // 현재 화면의 년/월과 다르면 해당 달로 이동 (URL 이동)
      if (
        currentDate.getFullYear() !== todayYear ||
        currentDate.getMonth() + 1 !== todayMonth
      ) {
        navigateTo(`${baseUrl}?year=${todayYear}&month=${todayMonth}`);
        return;
      }

      // 같은 달이면 그냥 오늘 날짜 선택
      $wrap.find(".schedule_table .table tbody td").removeClass("select");
      selectTodayIfVisible($wrap, currentDate, daysKor, updateSelectionInfo);
    });

    $wrap.find(".schedule_table .table tbody td").on("click", function () {
      const $cell = $(this);
      if ($cell.hasClass("disabled")) {
        return;
      }
      $wrap.find(".schedule_table .table tbody td").removeClass("select");
      $cell.addClass("select");
      updateSelectionInfo($cell);
    });

    function getInitialCount(status, programInfo) {
      const isVacation = programInfo && programInfo.isVacation;
      const isUnlimited = programInfo && programInfo.isUnlimited;

      if (status === "신청가능") {
        // 중등방학·고등방학: 신청 시작값 20으로 고정
        if (isVacation && !isUnlimited && programInfo.total > 0) {
          return 20;
        }
        return 10;
      } else if (status === "잔여석 신청 가능") {
        // 방학 프로그램이고 정원이 있으면 잔여석을 모두 채워야 함
        if (isVacation && !isUnlimited && programInfo.remaining > 0) {
          return programInfo.remaining;
        }
        return 4;
      }
      return 0;
    }

    /** minus 버튼으로 내려갈 수 있는 최소값(바닥). 방학 신청가능은 시작 20, 최소도 20. */
    function getMinimumCount(status, programInfo) {
      const isVacation = programInfo && programInfo.isVacation;
      const isUnlimited = programInfo && programInfo.isUnlimited;

      if (status === "신청가능") {
        if (isVacation && !isUnlimited && programInfo.total > 0) {
          return 20;
        }
        return 10;
      }
      if (status === "잔여석 신청 가능") {
        if (isVacation && !isUnlimited && programInfo.remaining > 0) {
          return programInfo.remaining;
        }
        return 4;
      }
      return 0;
    }

    function getSelectedProgramInfo() {
      const $selectedRow = $wrap
        .find('.glbox.select_day .tbl input[type="radio"]:checked')
        .closest("tr");
      if (!$selectedRow.length) {
        return null;
      }
      const total = parseInt($selectedRow.data("total"), 10) || 0;
      const applied = parseInt($selectedRow.data("applied"), 10) || 0;
      const isUnlimited =
        $selectedRow.data("isUnlimited") === 1 ||
        $selectedRow.data("is-unlimited") === 1 ||
        $selectedRow.data("isUnlimited") === "1" ||
        $selectedRow.data("is-unlimited") === "1";
      const isVacation =
        $selectedRow.data("isVacation") === 1 ||
        $selectedRow.data("is-vacation") === 1 ||
        $selectedRow.data("isVacation") === "1" ||
        $selectedRow.data("is-vacation") === "1";
      const remaining = isUnlimited ? 9999 : total - applied; // 잔여석
      return {
        status: $selectedRow.data("status") || "",
        total: total,
        applied: applied,
        remaining: remaining,
        isUnlimited: isUnlimited,
        isVacation: isVacation,
      };
    }

    // + 버튼 활성화/비활성화 업데이트
    function updatePlusButtonState() {
      const $plusBtn = $wrap.find(".btn.plus");
      const programInfo = getSelectedProgramInfo();
      const $input = $wrap.find(".count input");
      const currentValue = parseInt($input.val(), 10) || 0;

      if (!programInfo || !programInfo.status) {
        $plusBtn.prop("disabled", true);
        updateMinusButtonState();
        return;
      }

      if (programInfo.isUnlimited) {
        $plusBtn.prop("disabled", false);
        updateMinusButtonState();
        return;
      }

      if (programInfo.isVacation && programInfo.total > 0) {
        if (currentValue >= programInfo.total) {
          $plusBtn.prop("disabled", true);
        } else {
          $plusBtn.prop("disabled", false);
        }
        updateMinusButtonState();
        return;
      }

      if (currentValue < programInfo.remaining) {
        $plusBtn.prop("disabled", false);
      } else {
        $plusBtn.prop("disabled", true);
      }
      updateMinusButtonState();
    }

    // − 버튼: 현재값이 최소값보다 클 때만 활성화 (클릭 가능하도록)
    function updateMinusButtonState() {
      const $minusBtn = $wrap.find(".btn.minus");
      const programInfo = getSelectedProgramInfo();
      const $input = $wrap.find(".count input");
      const currentValue = parseInt($input.val(), 10) || 0;

      if (!programInfo || !programInfo.status) {
        $minusBtn.prop("disabled", true);
        return;
      }

      const minimumCount = getMinimumCount(programInfo.status, programInfo);
      if (currentValue <= minimumCount) {
        $minusBtn.prop("disabled", true);
      } else {
        $minusBtn.prop("disabled", false);
      }
    }

    $wrap.find(".btn.plus").on("click", function () {
      const programInfo = getSelectedProgramInfo();
      if (!programInfo || !programInfo.status) {
        return;
      }

      const $input = $wrap.find(".count input");
      const currentValue = parseInt($input.val(), 10) || 0;
      const initialCount = getInitialCount(programInfo.status, programInfo);

      if (currentValue < initialCount) {
        $input.val(initialCount);
        updatePlusButtonState();
        return;
      }

      // 제한없음인 경우
      if (programInfo.isUnlimited) {
        const newValue = currentValue + 1;
        $input.val(newValue);
        updatePlusButtonState();
        return;
      }

      // 정원이 있는 경우 잔여석 확인
      const newValue = currentValue + 1;
      if (newValue <= programInfo.remaining) {
        $input.val(newValue);
        updatePlusButtonState();
      } else {
        alert(
          "신청 가능한 인원을 초과했습니다. (잔여석: " +
            programInfo.remaining +
            "명)"
        );
      }
    });

    $wrap.find(".btn.minus").on("click", function () {
      const programInfo = getSelectedProgramInfo();
      if (!programInfo || !programInfo.status) {
        return;
      }

      const $input = $wrap.find(".count input");
      const currentValue = parseInt($input.val(), 10) || 0;
      const minimumCount = getMinimumCount(programInfo.status, programInfo);

      if (currentValue <= minimumCount) {
        $input.val(minimumCount);
        updatePlusButtonState();
        return;
      }

      const newValue = currentValue - 1;
      if (newValue >= minimumCount) {
        $input.val(newValue);
        updatePlusButtonState();
      }
    });

    $wrap
      .find(".glbox.select_day .tbl")
      .on("change", 'input[type="radio"]', function () {
        const $row = $(this).closest("tr");
        const status = $row.data("status") || "";

        // 선택된 행에서 직접 정보 가져오기
        const total = parseInt($row.data("total"), 10) || 0;
        const applied = parseInt($row.data("applied"), 10) || 0;
        const isUnlimited =
          $row.data("isUnlimited") === 1 ||
          $row.data("is-unlimited") === 1 ||
          $row.data("isUnlimited") === "1" ||
          $row.data("is-unlimited") === "1";
        const isVacation =
          $row.data("isVacation") === 1 ||
          $row.data("is-vacation") === 1 ||
          $row.data("isVacation") === "1" ||
          $row.data("is-vacation") === "1";
        const remaining = isUnlimited ? 9999 : total - applied;

        const programInfo = {
          status: status,
          total: total,
          applied: applied,
          remaining: remaining,
          isUnlimited: isUnlimited,
          isVacation: isVacation,
        };

        const initialCount = getInitialCount(status, programInfo);
        $wrap.find(".count input").val(initialCount > 0 ? initialCount : 0);

        // 방학 프로그램 안내 문구 업데이트
        updateVacationInfo(programInfo);

        selectedProgramData = {
          programId: $row.data("programId"),
          programName: $row.data("programName"),
          educationDate: $row.data("educationDate"),
          educationType: $row.data("educationType"),
          educationFee: $row.data("educationFee"),
        };

        // 프로그램 선택 시 + 버튼 상태 업데이트
        updatePlusButtonState();
      });

    $wrap
      .find('[data-layer-open="pop_approval"]')
      .on("click", function (event) {
        event.preventDefault();
        event.stopImmediatePropagation();

        if (!selectedProgramData || !selectedProgramData.programId) {
          alert("프로그램을 선택해주세요.");
          return;
        }

        // 접수기간 종료 체크
        const $selectedRow = $wrap.find(
          `tr[data-program-id="${selectedProgramData.programId}"]`
        );
        if ($selectedRow.length && $selectedRow.data("is-closed") === true) {
          alert("접수기간이 종료되어 신청할 수 없습니다.");
          return;
        }

        $approvalModal.fadeIn(300);
      });

    $("#group-submit-btn").on("click", function (event) {
      event.preventDefault();
      event.stopImmediatePropagation();

      const $agreementCheckbox = $("#agreement_checkbox");

      if (!$agreementCheckbox.is(":checked")) {
        alert("승인 안내 내용에 동의해주세요.");
        return;
      }

      if (!selectedProgramData || !selectedProgramData.programId) {
        alert("프로그램을 선택해주세요.");
        return;
      }

      const selectedInfo = getSelectedProgramInfo();
      if (!selectedInfo) {
        alert("프로그램을 선택해주세요.");
        return;
      }

      const defaultCount = getInitialCount(selectedInfo.status, selectedInfo);
      const applicantCount =
        parseInt($wrap.find(".count input").val(), 10) || defaultCount;

      // 방학 프로그램인 경우 정원을 모두 채워야 함
      if (
        selectedInfo.isVacation &&
        !selectedInfo.isUnlimited &&
        selectedInfo.total > 0
      ) {
        // 신청가능 상태면 정원을 모두 채워야 하고, 잔여석 신청 가능 상태면 잔여석을 모두 채워야 함
        const requiredCount =
          selectedInfo.status === "신청가능"
            ? selectedInfo.total
            : selectedInfo.remaining;
        if (applicantCount !== requiredCount) {
          const statusText =
            selectedInfo.status === "신청가능" ? "정원" : "잔여석";
          alert(
            `방학 프로그램은 ${statusText}(${requiredCount}명)을 모두 채워야 신청 가능합니다.`
          );
          return;
        }
      } else {
        // 일반 프로그램은 최소 4명
        if (applicantCount < 4) {
          alert("단체 신청은 최소 4명 이상이어야 합니다.");
          return;
        }
      }

      const csrfToken = $('meta[name="csrf-token"]').attr("content");

      if (!csrfToken) {
        alert("CSRF 토큰을 찾을 수 없습니다. 페이지를 새로고침해주세요.");
        return;
      }

      if (!applyUrl) {
        alert("신청 URL이 설정되지 않았습니다.");
        return;
      }

      $.ajax({
        url: applyUrl,
        method: "POST",
        headers: {
          "X-CSRF-TOKEN": csrfToken,
          Accept: "application/json",
        },
        data: {
          program_reservation_id: selectedProgramData.programId,
          applicant_count: applicantCount,
          agreement: 1,
        },
        success: function (response) {
          if (response.success && response.redirect_url) {
            window.location.href = response.redirect_url;
          } else {
            alert(response.message || "신청이 완료되었습니다.");
            if (response.redirect_url) {
              window.location.href = response.redirect_url;
            }
          }
        },
        error: function (xhr) {
          let errorMessage = "신청 중 오류가 발생했습니다.";

          if (xhr.responseJSON && xhr.responseJSON.message) {
            errorMessage = xhr.responseJSON.message;
          } else if (xhr.status === 401) {
            errorMessage = "로그인이 필요합니다.";
          } else if (xhr.status === 400) {
            errorMessage = "잘못된 요청입니다.";
          }

          alert(errorMessage);
        },
      });
    });
  }

  function loadTossScript() {
    return new Promise(function (resolve, reject) {
      if (window.TossPayments) {
        resolve();
        return;
      }
      const s = document.createElement("script");
      s.src = "/js/vendor/tosspayments-v2.js";
      s.onload = resolve;
      s.onerror = function () {
        reject(
          new Error(
            "결제 스크립트를 불러올 수 없습니다. (네트워크/방화벽 확인)"
          )
        );
      };
      document.head.appendChild(s);
    });
  }

  var currentTossPayment = null;

  function initIndividualSelect($container) {
    if (!$container.length) {
      return;
    }

    const $form = $container.find("#individual-filter-form");

    if ($form.length) {
      $form.find("select").on("change", function () {
        $form.trigger("submit");
      });
    }

    // 이벤트 바인딩은 initSelectPages에서 $(document) 레벨로 처리
  }

  function initIndividualApplyHandler() {
    $(document).off("click", ".js-individual-apply-btn");
    $(document).on("click", ".js-individual-apply-btn", function (e) {
      e.preventDefault();
      e.stopPropagation();

      const $btn = $(this);
      const $applyForm = $btn.closest("form.js-individual-apply-form");
      if (!$applyForm.length) {
        return false;
      }

      const $container = $applyForm.closest('[data-program-page="select"]');
      if (!$container.length) {
        $applyForm[0].submit();
        return false;
      }

      // 신청 확인 알럿
      if (!confirm("신청하시겠습니까?")) {
        return false;
      }

      const hasOnlineCard = $applyForm.attr("data-has-online-card") === "1";

      if (!hasOnlineCard) {
        $applyForm[0].submit();
        return false;
      }

      const prepareUrl = $container.attr("data-prepare-url");
      const csrf = $('meta[name="csrf-token"]').attr("content");
      const reservationId = $applyForm
        .find('input[name="program_reservation_id"]')
        .val();
      const participationDate = $applyForm
        .find('input[name="participation_date"]')
        .val();

      if (!prepareUrl || !csrf || !reservationId) {
        alert(
          "결제 정보를 확인할 수 없습니다. 페이지를 새로고침한 뒤 다시 시도해 주세요."
        );
        return false;
      }

      $.ajax({
        url: prepareUrl,
        method: "POST",
        headers: {
          "X-CSRF-TOKEN": csrf,
          Accept: "application/json",
        },
        data: {
          program_reservation_id: reservationId,
          participation_date: participationDate || undefined,
        },
        success: function (data) {
          loadTossScript()
            .then(function () {
              var customerKey =
                "lesec_" +
                Date.now() +
                "_" +
                Math.random().toString(36).substring(2, 12);
              var tossPayments = window.TossPayments(data.client_key);
              var widgets = tossPayments.widgets({ customerKey: customerKey });

              $("#toss-payment-widget").empty();
              $("#toss-agreement").empty();

              return widgets
                .setAmount({ currency: "KRW", value: data.amount })
                .then(function () {
                  return widgets.renderPaymentMethods({
                    selector: "#toss-payment-widget",
                    variantKey: "DEFAULT",
                  });
                })
                .then(function () {
                  return widgets.renderAgreement({
                    selector: "#toss-agreement",
                    variantKey: "AGREEMENT",
                  });
                })
                .then(function () {
                  currentTossPayment = {
                    widgets: widgets,
                    orderId: data.orderId,
                    orderName: "프로그램 신청",
                    successUrl: data.success_url,
                    failUrl: data.fail_url,
                  };
                  $("#pop_toss_payment").fadeIn(300);
                });
            })
            .catch(function (err) {
              alert(
                err && err.message
                  ? err.message
                  : "결제 창을 여는 데 실패했습니다."
              );
            });
        },
        error: function (xhr) {
          const msg =
            xhr.responseJSON && xhr.responseJSON.message
              ? xhr.responseJSON.message
              : "결제 준비에 실패했습니다. 다시 시도해 주세요.";

          if (xhr.status === 400 && msg && msg.includes("0원")) {
            $applyForm[0].submit();
            return;
          }

          alert(msg);
        },
      });

      return false;
    });

    $(document).off("click", "#toss-payment-submit-btn");
    $(document).on("click", "#toss-payment-submit-btn", function () {
      if (!currentTossPayment) {
        return;
      }
      var payload = currentTossPayment;
      var $btn = $(this);
      $btn.prop("disabled", true);
      payload.widgets
        .requestPayment({
          orderId: payload.orderId,
          orderName: payload.orderName,
          successUrl: payload.successUrl,
          failUrl: payload.failUrl,
        })
        .catch(function (err) {
          alert(err && err.message ? err.message : "결제 요청에 실패했습니다.");
          $btn.prop("disabled", false);
        });
    });

    $(document).on(
      "click",
      "[data-layer-close='pop_toss_payment']",
      function () {
        currentTossPayment = null;
      }
    );
  }

  function initSelectPages() {
    const $selectWraps = $('[data-program-page="select"]');
    if (!$selectWraps.length) {
      return;
    }

    $selectWraps.each(function () {
      const $wrap = $(this);
      const mode = getData($wrap, "selectMode", "");

      if (mode === "group") {
        initGroupSelect($wrap);
      }

      if (mode === "individual") {
        initIndividualSelect($wrap);
      }
    });
  }

  function initLayerHandlers() {
    $(document).on("click", "[data-layer-open]", function () {
      const targetId = $(this).data("layerOpen");
      if (!targetId) {
        return;
      }
      $(`#${targetId}`).fadeIn(300);
    });

    $(document).on("click", "[data-layer-close]", function () {
      const targetId = $(this).data("layerClose");
      if (!targetId) {
        return;
      }
      $(`#${targetId}`).fadeOut(300);
    });
  }

  function initCompletionNavigation() {
    $(document).on("click", "[data-navigate-complete]", function () {
      const url = $(this).data("navigateComplete");
      navigateTo(url);
    });
  }

  $(function () {
    initLayerHandlers();
    initCompletionNavigation();
    initApplyPages();
    initIndividualApplyHandler();
    initSelectPages();
  });
})(jQuery);

/* 일정 캘린더 동일 프로그램 병합 처리 */
(function ($) {
  function applyScheduleMerge() {
    $(".schedule_table tbody tr").each(function () {
      const programMap = {};

      $(this)
        .find("td")
        .each(function (tdIndex) {
          $(this)
            .find("ul.list li")
            .each(function () {
              const $li = $(this);
              const programId = $li.data("programId");

              if (!programId) {
                return;
              }

              if (!programMap[programId]) {
                programMap[programId] = [];
              }

              programMap[programId].push({
                $el: $li,
                $td: $li.closest("td"),
                tdIndex: tdIndex,
              });
            });
        });

      $.each(programMap, function (_, items) {
        if (items.length <= 1) {
          return;
        }

        const $first = items[0].$el;
        const count = items.length;

        // 1. width 계산
        const gap = 9;
        const width =
          count === 1
            ? "100%"
            : `calc(${count * 100}% + ${(count - 1) * gap}px)`;

        $first.css("width", width);

        // 2. 이후 항목 off 처리
        for (let i = 1; i < items.length; i++) {
          items[i].$el.addClass("off");
          // overflow(disabled) 셀의 회색 배경이 앞 셀 막대를 가리지 않도록 배경 투명화
          if (items[i].$td.hasClass("disabled")) {
            items[i].$td.css("background-color", "transparent");
          }
        }
      });
    });
  }

  $(function () {
    applyScheduleMerge();
  });
})(jQuery);

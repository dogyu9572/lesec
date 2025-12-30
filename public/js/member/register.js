(function ($) {
    'use strict';

    function MemberRegister(form) {
        this.$form = $(form);
        this.duplicateUrl = this.$form.data('duplicateUrl');
        this.schoolSearchUrl = this.$form.data('schoolSearchUrl');
        this.$modal = $(this.$form.data('schoolModal'));
        this.selectedSchoolId = (this.$form.find('.input_school_id').val() || '').trim() || null;
        this.schoolDataLoaded = false;
        this.serverErrors = this.$form.data('errors') || null;
        this.serverErrorKeys = this.serverErrors ? Object.keys(this.serverErrors) : [];
        this.schoolPaginationMeta = null;
        this.currentSchoolPage = 1;

        this.init();
    }

    MemberRegister.prototype.init = function () {
        this.bindDuplicateCheck();
        this.bindPhoneFormatting();
        this.bindEmailDomainToggle();
        this.bindSchoolModal();
        this.bindInputChange();
        this.showServerInlineErrors();
        this.focusServerErrorField();
        // 페이지 로드 시 중복 확인 상태 복원
        this.restoreDuplicateCheckStatus();
        // 페이지 로드 시 학교 데이터 미리 불러오기
        this.preloadSchools();
    };

    MemberRegister.prototype.getCsrfToken = function () {
        return $('meta[name="csrf-token"]').attr('content');
    };

    MemberRegister.prototype.bindDuplicateCheck = function () {
        var self = this;

        this.$form.on('click', '.js-duplicate-check', function (event) {
            event.preventDefault();
            if (!self.duplicateUrl) {
                return;
            }

            var $button = $(this);
            var field = $button.data('field');
            var value = '';

            if ($button.data('email')) {
                var $emailId = self.$form.find($button.data('emailId'));
                var $emailDomain = self.$form.find($button.data('emailDomain'));
                var $emailCustom = self.$form.find($button.data('emailCustom'));
                var idValue = $emailId.length ? ($emailId.val() || '').trim() : '';
                var domainValue = $emailDomain.length ? ($emailDomain.val() || '').trim() : '';

                if (domainValue === 'custom' && $emailCustom.length) {
                    domainValue = ($emailCustom.val() || '').trim();
                }

                if (idValue && domainValue) {
                    value = idValue + '@' + domainValue;
                }
            } else {
                var inputSelector = $button.data('input');
                if (inputSelector) {
                    value = (self.$form.find(inputSelector).val() || '').trim();
                } else if (field) {
                    value = (self.$form.find('[name="' + field + '"]').val() || '').trim();
                }
            }

            var $wrapper = $button.closest('dd');
            var $error = $wrapper.find('.error_alert').first();
            var $success = $wrapper.find('.success_alert');

            if (field === 'contact') {
                value = self.normalizePhone(value);
            }

            if (!field || !value) {
                showResult(false, '값을 입력해주세요.');
                return;
            }

            $button.prop('disabled', true).text('확인중...');

            $.ajax({
                url: self.duplicateUrl,
                method: 'POST',
                data: {
                    field: field,
                    value: value
                },
                headers: {
                    'X-CSRF-TOKEN': self.getCsrfToken()
                },
                dataType: 'json'
            })
                .done(function (response) {
                    var success = response && response.success;
                    var message = response && response.message
                        ? response.message
                        : (success ? '사용 가능한 값입니다.' : '중복 확인에 실패했습니다.');
                    showResult(success, message);
                })
                .fail(function () {
                    showResult(false, '중복 확인 중 오류가 발생했습니다.');
                })
                .always(function () {
                    $button.prop('disabled', false).text('중복 확인');
                });

            function showResult(success, message) {
                if ($success.length) {
                    $success.remove();
                }

                if (success) {
                    // 중복 확인 관련 에러 메시지 모두 제거
                    $wrapper.find('.error_alert').each(function () {
                        var errorText = $(this).text();
                        if (errorText.indexOf('중복 확인') !== -1) {
                            $(this).remove();
                        } else {
                            $(this).hide();
                        }
                    });
                    
                    $wrapper.append('<p class="success_alert c_green">' + message + '</p>');
                    
                    // 중복 확인 성공 시 verified 상태 설정
                    if (field === 'login_id') {
                        self.$form.find('[name="login_id_verified"]').val('1');
                    } else if (field === 'contact') {
                        self.$form.find('[name="contact_verified"]').val('1');
                    }
                } else {
                    if (!$error.length) {
                        $error = $('<p class="error_alert"></p>').appendTo($wrapper);
                    }
                    $error.text(message).show();
                    
                    // 중복 확인 실패 시 verified 상태 리셋
                    if (field === 'login_id') {
                        self.$form.find('[name="login_id_verified"]').val('0');
                    } else if (field === 'contact') {
                        self.$form.find('[name="contact_verified"]').val('0');
                    }
                }
            }
        });
    };

    MemberRegister.prototype.bindEmailDomainToggle = function () {
        var $domainSelects = this.$form.find('.email-domain-select');

        $domainSelects.each(function () {
            var $select = $(this);
            var $container = $select.closest('dd');
            var $custom = $container.find('.email-domain-custom');

            toggleCustomField($select, $custom);

            $select.on('change', function () {
                toggleCustomField($select, $custom);
            });
        });

        function toggleCustomField($select, $custom) {
            if (!$custom.length) {
                return;
            }

            if ($select.val() === 'custom') {
                $custom.show().focus();
            } else {
                $custom.hide().val('');
            }
        }
    };

    MemberRegister.prototype.bindInputChange = function () {
        var self = this;
        
        // 아이디 입력값 변경 시 verified 리셋
        this.$form.on('input', '[name="login_id"]', function () {
            var $input = $(this);
            var currentValue = $input.val().trim();
            var originalValue = $input.data('original-login-id') || '';
            
            if (currentValue !== originalValue) {
                self.$form.find('[name="login_id_verified"]').val('0');
                $input.closest('dd').find('.success_alert').remove();
            }
        });
        
        // 연락처 입력값 변경 시 verified 리셋
        this.$form.on('input', '[name="contact"], [name="student_contact"]', function () {
            var $input = $(this);
            var currentValue = self.normalizePhone($input.val());
            var originalValue = $input.data('original-contact') || '';
            
            if (currentValue !== originalValue) {
                self.$form.find('[name="contact_verified"]').val('0');
                $input.closest('dd').find('.success_alert').remove();
            }
        });
    };

    MemberRegister.prototype.bindPhoneFormatting = function () {
        var self = this;
        var $inputs = this.$form.find('[data-phone-input]');

        if (!$inputs.length) {
            return;
        }

        $inputs.each(function () {
            var $input = $(this);
            var initial = self.formatPhone(self.normalizePhone($input.val()));
            if (initial) {
                $input.val(initial);
            }

            $input.on('input', function (e) {
                var input = this;
                var cursorPos = input.selectionStart;
                var oldValue = $input.val();
                var oldDigits = self.normalizePhone(oldValue);
                
                var newDigits = self.normalizePhone($input.val());
                var formatted = self.formatPhone(newDigits);
                
                if (formatted === oldValue) {
                    return;
                }
                
                $input.val(formatted);
                
                // 커서 위치 보정
                var addedHyphens = 0;
                for (var i = 0; i < cursorPos && i < formatted.length; i++) {
                    if (formatted[i] === '-') {
                        addedHyphens++;
                    }
                }
                
                var digitsBefore = self.normalizePhone(oldValue.substring(0, cursorPos)).length;
                var newCursorPos = 0;
                var digitCount = 0;
                
                for (var i = 0; i < formatted.length; i++) {
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

            $input.on('blur', function () {
                var digits = self.normalizePhone($input.val());
                $input.val(self.formatPhone(digits));
            });
        });
    };

    MemberRegister.prototype.focusServerErrorField = function () {
        if (!this.serverErrorKeys.length) {
            return;
        }

        var selector = this.getErrorFieldSelector(this.serverErrorKeys[0]);
        if (!selector) {
            return;
        }

        this.focusErrorField(selector);
        this.$form.removeData('errors');
        this.serverErrors = null;
        this.serverErrorKeys = [];
    };

    MemberRegister.prototype.showServerInlineErrors = function () {
        this.$form.find('.error_alert').each(function () {
            var $error = $(this);
            if ($.trim($error.text()) !== '') {
                $error.show();
            }
        });
    };

    /**
     * 페이지 로드 시 중복 확인 완료 상태 복원
     */
    MemberRegister.prototype.restoreDuplicateCheckStatus = function () {
        var self = this;

        // 아이디 중복 확인 상태 복원
        var loginIdVerified = this.$form.find('[name="login_id_verified"]').val();
        if (loginIdVerified === '1') {
            var $loginIdWrapper = this.$form.find('[name="login_id"]').closest('dd');
            var $loginIdInput = this.$form.find('[name="login_id"]');
            var loginIdValue = $loginIdInput.val();
            
            if (loginIdValue && loginIdValue.trim() !== '') {
                // 중복 확인 관련 에러 메시지 제거
                $loginIdWrapper.find('.error_alert').each(function () {
                    var errorText = $(this).text();
                    if (errorText.indexOf('중복 확인') !== -1) {
                        $(this).remove();
                    }
                });
                
                // 성공 메시지 표시
                if (!$loginIdWrapper.find('.success_alert').length) {
                    $loginIdWrapper.append('<p class="success_alert c_green">사용 가능한 아이디 입니다.</p>');
                }
            }
        }

        // 연락처 중복 확인 상태 복원
        var contactVerified = this.$form.find('[name="contact_verified"]').val();
        if (contactVerified === '1') {
            var $contactWrapper = this.$form.find('[name="student_contact"]').closest('dd');
            var $contactInput = this.$form.find('[name="student_contact"]');
            var contactValue = $contactInput.val();
            
            if (contactValue && contactValue.trim() !== '') {
                // 중복 확인 관련 에러 메시지 제거
                $contactWrapper.find('.error_alert').each(function () {
                    var errorText = $(this).text();
                    if (errorText.indexOf('중복 확인') !== -1) {
                        $(this).remove();
                    }
                });
                
                // 성공 메시지 표시
                if (!$contactWrapper.find('.success_alert').length) {
                    $contactWrapper.append('<p class="success_alert c_green">사용 가능한 연락처 입니다.</p>');
                }
            }
        }
    };

    MemberRegister.prototype.getErrorFieldSelector = function (field) {
        var mapping = {
            login_id: "[name='login_id']",
            password: "[name='password']",
            password_confirmation: "[name='password_confirmation']",
            name: "[name='name']",
            birth_date: "[name='birth_date']",
            gender: "[name='gender']",
            contact: "[name='student_contact'], [name='contact']",
            student_contact: "[name='student_contact']",
            parent_contact: "[name='parent_contact']",
            email: "[name='email_id'], [name='email']",
            email_id: "[name='email_id']",
            email_domain: "[name='email_domain']",
            school_name: "[name='school_name']",
            school_id: "[name='school_name']",
            grade: "[name='grade']",
            class_number: "[name='class_number']",
            privacy_agree: "[name='privacy_agree']",
            notification_agree: "[name='notification_agree']",
            city: "[name='city']",
            district: "[name='district']"
        };

        return mapping[field] || "[name='" + field + "']";
    };

    MemberRegister.prototype.focusErrorField = function (selector) {
        var $target = this.$form.find(selector).filter(':enabled:visible').first();

        if (!$target.length) {
            $target = this.$form.find(selector).first();
        }

        if (!$target.length) {
            return;
        }

        var element = $target.get(0);

        if (element.scrollIntoView) {
            element.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        setTimeout(function () {
            if (typeof element.focus === 'function') {
                element.focus({ preventScroll: true });
            } else {
                $target.focus();
            }
        }, 150);
    };

    MemberRegister.prototype.normalizePhone = function (value) {
        return (value || '').replace(/[^0-9]/g, '');
    };

    MemberRegister.prototype.formatPhone = function (digits) {
        if (!digits) {
            return '';
        }

        var len = digits.length;

        if (len <= 3) {
            return digits;
        }

        if (len <= 7) {
            return digits.substring(0, 3) + '-' + digits.substring(3);
        }

        var first = digits.substring(0, 3);
        var second = digits.substring(3, 7);
        var third = digits.substring(7, 11);

        return first + '-' + second + (third ? '-' + third : '');
    };

    MemberRegister.prototype.bindSchoolModal = function () {
        var self = this;

        if (!this.$modal.length || !this.schoolSearchUrl) {
            return;
        }

        // 모달 열릴 때 자동으로 학교 목록 불러오기
        this.$modal.on('popup:show', function () {
            // 입력 버튼 초기화 (disabled)
            self.$modal.find('.btn_input_school').prop('disabled', true);
            if (!self.schoolDataLoaded) {
                self.fetchSchools(false);
            }
        });

        this.$modal.on('click', '.btn_search_school', function (event) {
            event.preventDefault();
            self.currentSchoolPage = 1;
            self.fetchSchools(true);
        });

        this.$modal.on('change', '.search_city', function () {
            self.$modal.find('.search_district').val('');
            self.currentSchoolPage = 1;
            self.fetchSchools(true);
        });

        this.$modal.on('change', '.search_level', function () {
            self.currentSchoolPage = 1;
            self.fetchSchools(true);
        });

        this.$modal.on('keypress', '.search_keyword', function (event) {
            if (event.which === 13) {
                event.preventDefault();
                self.currentSchoolPage = 1;
                self.fetchSchools(true);
            }
        });

        this.$modal.on('click', '.school_results tr', function (event) {
            if ($(event.target).is('input, label, i, button, a')) {
                return;
            }
            var $radio = $(this).find('input[name="selected_school"]');
            if ($radio.length) {
                $radio.prop('checked', true).trigger('change');
            }
        });

        this.$modal.on('change', 'input[name="selected_school"]', function () {
            var $row = $(this).closest('tr');
            self.selectedSchoolId = $row.data('id');
            self.updateResultMessage(null, $row.data('name'));
        });

        this.$modal.on('click', '.btn_select_school', function (event) {
            event.preventDefault();
            self.confirmSchoolSelection();
        });

        this.$modal.on('click', '.btn_input_school', function (event) {
            event.preventDefault();
            self.inputSchoolName();
        });

        // 페이지네이션 클릭 이벤트
        this.$modal.on('click', '.school_pagination a[data-page]', function (event) {
            event.preventDefault();
            var $link = $(this);
            if ($link.hasClass('on') || $link.attr('onclick') === 'return false;') {
                return;
            }
            var page = parseInt($link.data('page'), 10);
            if (page && page > 0) {
                self.currentSchoolPage = page;
                self.fetchSchools(true);
            }
        });
    };

    MemberRegister.prototype.preloadSchools = function () {
        // 페이지 로드 시 백그라운드에서 학교 데이터 미리 불러오기
        if (this.$modal.length && this.schoolSearchUrl) {
            this.fetchSchools(false);
        }
    };

    MemberRegister.prototype.openModal = function () {
        var self = this;

        // 데이터 로드 완료 여부와 관계없이 모달 바로 표시
        this.$modal.fadeIn(300, function () {
            self.$modal.trigger('popup:show');
        });
        
        this.highlightSelectedSchool();
    };

    MemberRegister.prototype.closeModal = function () {
        var self = this;

        this.$modal.fadeOut(300, function () {
            self.$modal.trigger('popup:hide');
        });
    };

    MemberRegister.prototype.fetchSchools = function (force, callback) {
        var self = this;

        if (!this.schoolSearchUrl) {
            if (callback) callback();
            return;
        }

        if (this.schoolDataLoaded && force !== true) {
            if (callback) callback();
            return;
        }

        var params = {
            city: this.$modal.find('.search_city').val(),
            district: this.$modal.find('.search_district').val(),
            school_level: this.$modal.find('.search_level').val(),
            keyword: this.$modal.find('.search_keyword').val(),
            page: this.currentSchoolPage || 1
        };

        // 로딩 중 표시
        var $tbody = this.$modal.find('.school_results');
        $tbody.html('<tr><td colspan="3" style="text-align:center;padding:40px 0;color:#999;">학교 정보를 불러오는 중입니다...</td></tr>');
        this.$modal.addClass('loading');

        $.ajax({
            url: this.schoolSearchUrl,
            method: 'GET',
            data: params,
            dataType: 'json'
        })
            .done(function (data) {
                self.schoolDataLoaded = true;
                var list = (data && data.data) || [];
                self.renderSchools(list);
                self.updateResultMessage(data && data.meta ? data.meta.count : list.length);

                // 페이지네이션 메타 정보 저장
                if (data && data.meta) {
                    self.schoolPaginationMeta = data.meta;
                    self.currentSchoolPage = data.meta.current_page || 1;
                    self.renderPagination(data.meta);
                } else {
                    self.schoolPaginationMeta = null;
                    self.$modal.find('.school_pagination').hide().empty();
                }

                if (data && data.filters) {
                    if (data.filters.cities) {
                        self.setSelectOptions(self.$modal.find('.search_city'), data.filters.cities, '전체');
                    }
                    if (data.filters.districts) {
                        self.setSelectOptions(self.$modal.find('.search_district'), data.filters.districts, '전체');
                    }
                    if (data.filters.levels) {
                        self.setSelectOptions(self.$modal.find('.search_level'), data.filters.levels, '전체');
                    }
                }

                self.highlightSelectedSchool();
            })
            .fail(function () {
                self.renderSchools([]);
                self.updateResultMessage(0);
                self.schoolPaginationMeta = null;
                self.$modal.find('.school_pagination').hide().empty();
                window.alert('학교 정보를 불러오는 중 오류가 발생했습니다.');
            })
            .always(function () {
                self.$modal.removeClass('loading');
                if (callback) callback();
            });
    };

    MemberRegister.prototype.renderSchools = function (list) {
        var $tbody = this.$modal.find('.school_results');
        var $inputBtn = this.$modal.find('.btn_input_school');
        $tbody.empty();

        if (!list.length) {
            $tbody.append('<tr class="empty"><td colspan="3">검색 결과가 없습니다.</td></tr>');
            // 검색 결과가 없을 때 입력 버튼 활성화
            $inputBtn.prop('disabled', false);
            return;
        }

        // 검색 결과가 있을 때 입력 버튼 비활성화
        $inputBtn.prop('disabled', true);

        var self = this;
        list.forEach(function (school) {
            var isSelected = self.selectedSchoolId && String(self.selectedSchoolId) === String(school.id);
            var rowHtml = '' +
                '<tr data-id="' + school.id + '" data-name="' + (school.name || '') + '" data-city="' + (school.city || '') +
                '" data-district="' + (school.district || '') + '" data-level="' + (school.level || '') + '">' +
                '<td>' + (school.city || '-') + '</td>' +
                '<td><strong>' + (school.name || '') + '</strong></td>' +
                '<td><label class="check solo"><input type="radio" name="selected_school" value="' + school.id + '"' +
                (isSelected ? ' checked' : '') + '><i></i></label></td>' +
                '</tr>';

            $tbody.append(rowHtml);
        });
    };

    MemberRegister.prototype.renderPagination = function (meta) {
        var self = this;
        var $pagination = this.$modal.find('.school_pagination');
        
        if (!meta || meta.last_page <= 1) {
            $pagination.hide().empty();
            return;
        }

        var currentPage = meta.current_page || 1;
        var lastPage = meta.last_page || 1;
        
        // 페이지 범위 계산 (현재 페이지 기준 앞뒤 2페이지씩)
        var start = Math.max(1, currentPage - 2);
        var end = Math.min(lastPage, start + 4);
        start = Math.max(1, end - 4);

        var html = '';
        
        // 맨끝 (첫 페이지)
        if (currentPage === 1) {
            html += '<a href="#" class="arrow two first" data-page="1" onclick="return false;">맨끝</a>';
        } else {
            html += '<a href="#" class="arrow two first" data-page="1">맨끝</a>';
        }
        
        // 이전
        if (currentPage === 1) {
            html += '<a href="#" class="arrow one prev" data-page="1" onclick="return false;">이전</a>';
        } else {
            html += '<a href="#" class="arrow one prev" data-page="' + (currentPage - 1) + '">이전</a>';
        }
        
        // 페이지 번호들
        for (var page = start; page <= end; page++) {
            if (page === currentPage) {
                html += '<a href="#" class="on" data-page="' + page + '" onclick="return false;">' + page + '</a>';
            } else {
                html += '<a href="#" data-page="' + page + '">' + page + '</a>';
            }
        }
        
        // 다음
        if (currentPage >= lastPage) {
            html += '<a href="#" class="arrow one next" data-page="' + lastPage + '" onclick="return false;">다음</a>';
        } else {
            html += '<a href="#" class="arrow one next" data-page="' + (currentPage + 1) + '">다음</a>';
        }
        
        // 맨끝 (마지막 페이지)
        if (currentPage >= lastPage) {
            html += '<a href="#" class="arrow two last" data-page="' + lastPage + '" onclick="return false;">맨끝</a>';
        } else {
            html += '<a href="#" class="arrow two last" data-page="' + lastPage + '">맨끝</a>';
        }
        
        $pagination.html(html).show();
    };

    MemberRegister.prototype.setSelectOptions = function ($select, items, placeholder) {
        if (!$select.length) {
            return;
        }

        var current = $select.val();
        var html = '';

        if (placeholder !== false) {
            html += '<option value="">' + (placeholder || '전체') + '</option>';
        }

        items.forEach(function (item) {
            if (!item) {
                return;
            }
            var value, label;
            if (typeof item === 'object') {
                value = item.value;
                label = item.label || item.value;
            } else {
                value = item;
                label = item;
            }

            if (value === undefined || value === null || value === '') {
                return;
            }

            html += '<option value="' + value + '">' + label + '</option>';
        });

        $select.html(html);

        if (current && $select.find('option[value="' + current + '"]').length) {
            $select.val(current);
        }
    };

    MemberRegister.prototype.updateResultMessage = function (count, selectedName) {
        var $message = this.$modal.find('.search_result_message');
        if (!$message.length) {
            return;
        }

        $message.hide().text('');
    };

    MemberRegister.prototype.highlightSelectedSchool = function () {
        if (!this.selectedSchoolId) {
            return;
        }

        this.$modal
            .find('input[name="selected_school"][value="' + this.selectedSchoolId + '"]')
            .prop('checked', true)
            .trigger('change');
    };

    MemberRegister.prototype.confirmSchoolSelection = function () {
        var $checked = this.$modal.find('input[name="selected_school"]:checked');
        if (!$checked.length) {
            window.alert('학교를 선택해주세요.');
            return;
        }

        var $row = $checked.closest('tr');
        this.selectedSchoolId = $row.data('id');
        this.applySchool({
            id: $row.data('id'),
            name: $row.data('name'),
            city: $row.data('city'),
            district: $row.data('district')
        });
        this.closeModal();
    };

    MemberRegister.prototype.applySchool = function (school) {
        this.$form.find('.input_school').val(school.name || '');
        this.$form.find('.input_school_id').val(school.id || '');

        var $citySelect = this.$form.find('select.city_select');
        var $cityHidden = this.$form.find('input.city_hidden');
        var $districtSelect = this.$form.find('select.district_select');
        var $districtHidden = this.$form.find('input.district_hidden');

        if ($citySelect.length && school.city) {
            if (!$citySelect.find('option[value="' + school.city + '"]').length) {
                $citySelect.append('<option value="' + school.city + '">' + school.city + '</option>');
            }
            $citySelect.val(school.city);
            if ($cityHidden.length) {
                $cityHidden.val(school.city);
            }
        }

        if ($districtSelect.length && school.district) {
            if (!$districtSelect.find('option[value="' + school.district + '"]').length) {
                $districtSelect.append('<option value="' + school.district + '">' + school.district + '</option>');
            }
            $districtSelect.val(school.district);
            if ($districtHidden.length) {
                $districtHidden.val(school.district);
            }
        }
    };

    MemberRegister.prototype.inputSchoolName = function () {
        var schoolName = this.$modal.find('.search_keyword').val().trim();
        if (!schoolName) {
            window.alert('학교명을 입력해주세요.');
            return;
        }

        // 검색한 학교명을 부모 페이지에 전달
        this.$form.find('.input_school').val(schoolName);
        this.$form.find('.input_school_id').val(''); // 학교 ID는 없으므로 빈 값

        // 시/도 정보가 있으면 함께 설정
        var city = this.$modal.find('.search_city').val();
        var district = this.$modal.find('.search_district').val();

        if (city || district) {
            var $citySelect = this.$form.find('select.city_select');
            var $cityHidden = this.$form.find('input.city_hidden');
            var $districtSelect = this.$form.find('select.district_select');
            var $districtHidden = this.$form.find('input.district_hidden');

            if (city && $citySelect.length) {
                if (!$citySelect.find('option[value="' + city + '"]').length) {
                    $citySelect.append('<option value="' + city + '">' + city + '</option>');
                }
                $citySelect.val(city);
                if ($cityHidden.length) {
                    $cityHidden.val(city);
                }
            }

            if (district && $districtSelect.length) {
                if (!$districtSelect.find('option[value="' + district + '"]').length) {
                    $districtSelect.append('<option value="' + district + '">' + district + '</option>');
                }
                $districtSelect.val(district);
                if ($districtHidden.length) {
                    $districtHidden.val(district);
                }
            }
        }

        this.closeModal();
    };

    $(function () {
        $('.js-member-register').each(function () {
            if (!$(this).data('memberRegisterInit')) {
                $(this).data('memberRegisterInit', true);
                new MemberRegister(this);
            }
        });
    });

    // 전역 팝업 함수 (기존 퍼블리싱 호환)
    if (typeof window.layerShow !== 'function') {
        window.layerShow = function (id) {
            var $target = $('#' + id);
            if ($target.length) {
                $target.fadeIn(300, function () {
                    $target.trigger('popup:show');
                });
            }
        };
    }

    if (typeof window.layerHide !== 'function') {
        window.layerHide = function (id) {
            var $target = $('#' + id);
            if ($target.length) {
                $target.fadeOut(300, function () {
                    $target.trigger('popup:hide');
                });
            }
        };
    }
})(window.jQuery);



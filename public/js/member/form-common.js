(function ($) {
    'use strict';

    function MemberBaseForm(form) {
        this.$form = $(form);
        this.init();
    }

    MemberBaseForm.prototype.init = function () {
        this.showErrors();
        this.focusFirstError();
        this.bindPhoneFormatting();
    };

    MemberBaseForm.prototype.showErrors = function () {
        this.$form.closest('.inputs').find('.error_alert').each(function () {
            var $error = $(this);
            if ($.trim($error.text()) !== '') {
                $error.show();
            }
        });
    };

    MemberBaseForm.prototype.focusFirstError = function () {
        var $error = this.$form.find('.error_alert').filter(function () {
            return $.trim($(this).text()) !== '';
        }).first();

        if (!$error.length) {
            return;
        }

        var $target = $error.prevAll('input, select, textarea').first();

        if (!$target.length) {
            $target = this.$form.find('input, select, textarea').first();
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

    MemberBaseForm.prototype.bindPhoneFormatting = function () {
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

            $input.on('input', function () {
                var cursorPos = this.selectionStart;
                var oldValue = $input.val();

                var digits = self.normalizePhone(oldValue);
                var formatted = self.formatPhone(digits);

                if (formatted === oldValue) {
                    return;
                }

                $input.val(formatted);

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

                if (newCursorPos < formatted.length && formatted[newCursorPos] === '-') {
                    newCursorPos++;
                }

                this.setSelectionRange(newCursorPos, newCursorPos);
            });

            $input.on('blur', function () {
                var digits = self.normalizePhone($input.val());
                $input.val(self.formatPhone(digits));
            });
        });
    };

    MemberBaseForm.prototype.normalizePhone = function (value) {
        return (value || '').replace(/[^0-9]/g, '');
    };

    MemberBaseForm.prototype.formatPhone = function (digits) {
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

    $(function () {
        $('.js-member-form').each(function () {
            if (!$(this).data('memberBaseFormInit')) {
                $(this).data('memberBaseFormInit', true);
                new MemberBaseForm(this);
            }
        });
    });
})(window.jQuery);



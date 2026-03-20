$(function () {
    $('.faq_wrap').on('click', '.faq-toggle-button', function () {
        var $button = $(this);
        var $item = $button.closest('dl');
        var $content = $item.children('dd');
        var isOpen = $item.hasClass('on');

        $item
            .siblings('dl')
            .removeClass('on')
            .children('dd')
            .stop(false, true)
            .slideUp('fast');

        $item
            .siblings('dl')
            .find('.faq-toggle-button')
            .attr('aria-expanded', 'false');

        if (isOpen) {
            $content.stop(false, true).slideUp('fast');
            $item.removeClass('on');
            $button.attr('aria-expanded', 'false');
            return;
        }

        $content.stop(false, true).slideDown('fast');
        $item.addClass('on');
        $button.attr('aria-expanded', 'true');
    });
});

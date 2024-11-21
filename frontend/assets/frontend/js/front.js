(function ($) {
    $(document).ready(function () {
        $('.pq').click(function () {
            $('html, body').animate({scrollTop: $("#question_" + $(this).data('q')).offset().top - 70}, 300)
        })
    })
})(jQuery);
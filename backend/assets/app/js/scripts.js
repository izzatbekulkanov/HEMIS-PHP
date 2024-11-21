(function ($) {
    $(document).ready(function () {
        $(document).on("keydown", function (e) {
            if (e.target.value) {
                if ((e.metaKey || e.ctrlKey) && (String.fromCharCode(e.which).toLowerCase() === 'b')) {
                    $(e.target).val(convertToCyrill(e.target.value));
                }
                if ((e.metaKey || e.ctrlKey) && (String.fromCharCode(e.which).toLowerCase() === 'l')) {
                    $(e.target).val(convertToLatin(e.target.value));
                }
            }
        });

        $('.checkbo').checkBo();
        setFocusToSearch();
        setDeleteButton();
        setConfirmAction();
        //setTrashButton();
        $('input[type="checkbox"].icheckbox').iCheck({
            checkboxClass: 'icheckbox_flat-blue',
            radioClass: 'iradio_flat-blue'
        });

        $('input[type="radio"].icheckbox').iCheck({
            checkboxClass: 'icheckbox_flat-blue',
            radioClass: 'iradio_flat-blue'
        });
        $('.mobile-phone').formatter({
            'pattern': '+(998) {{99}} {{999}}-{{99}}-{{99}}',
            'persistent': true
        });

        $('.landline-phone').formatter({
            'pattern': '+(0) {{999}} {{999}}-{{99}}-{{99}}',
            'persistent': true
        });

        var $loading = $('#loader').hide();
        var $scrollTop = 0;
        $(document)
            .ajaxStart(function () {
                //$scrollTop = $(document).scrollTop()
                //$loading.show();
            })
            .ajaxStop(function () {
                $loading.hide();
                //$(window).scrollTop($scrollTop)
            })

        $('[data-toggle="tooltip"]').tooltip();
    });
    $(document).on('ready pjax:success', function () {
        //setFocusToSearch();
        setDeleteButton();
        $('.selectable-row tr').on('click', function () {
            //$(this).find('.checkbox input:first-child').click();
        });
        $('input[type="checkbox"].icheckbox').iCheck({
            checkboxClass: 'icheckbox_flat-blue',
            radioClass: 'iradio_flat-blue'
        });
        $('.sticky-sidebar').theiaStickySidebar({
            additionalMarginTop: 20 + parseInt($('.sticky-sidebar').data('padding')),
            additionalMarginBottom: 20
        });
        setSameHeight();
    });
    $(document).on('expanded.pushMenu', function () {
        Cookies.set('sm_menu', '0');
    });
    $(document).on('collapsed.pushMenu', function () {
        Cookies.set('sm_menu', '1');
    });
    $('.sticky-sidebar').theiaStickySidebar({
        additionalMarginTop: 20 + parseInt($('.sticky-sidebar').data('padding')),
        additionalMarginBottom: 20
    });
    setSameHeight();
})(jQuery);

function setSameHeight() {
    if (document.width < 762) return;

    $('.sh-parent').each(function () {
        var items = $(this).find('.sh');
        var max = 0;
        items.each(function () {
            if (max < $(this).height()) {
                max = $(this).height();
            }
        });
        $(this).find('.sh').height(max);
    })
}

function setDeleteButton() {
    $('.btn-delete').on('click', function () {
        if (confirm($(this).data('message') != undefined ? $(this).data('message') : globalMessages.deleteItem)) {
            if ($(this).attr('action').length > 0) {
                document.location.href = $(this).attr('action');
            } else {
                return true;
            }
        }
        return false;
    });
}

function sortTable(elementContainer, link) {
    $(elementContainer).sortable({
        handle: '.sort_handle',
        update: function (event, ui) {
            var checked = [];

            $(elementContainer + " tr").each(function (index, element) {
                checked.push($(element).data('key'));
            });

            if (checked.length > 0) {
                var data = {};
                data.data = checked;
                data._csrf = $('input[name="_csrf"]').val();
                $.post(link, data, function () {
                })
            }
        }
    });
}

function setTrashButton() {
    $('.btn-trash').on('click', function () {
        if (confirm('Are you sure move to trash?')) {
            if ($(this).attr('action').length > 0) {
                document.location.href = $(this).attr('action');
            } else {
                return true;
            }
        }
        return false;
    });
}

function setConfirmAction() {
    $('.btn-confirm').on('click', function () {
        if (confirm($(this).data('message') !== undefined ? $(this).data('message') : 'Are you sure do this action?')) {
            if ($(this).data('action') && $(this).data('action').length > 0) {
                document.location.href = $(this).data('action');
            } else {
                return true;
            }
        }
        return false;
    });
}

function setFocusToSearch() {
    var input = $("input[name*='[search]'][type='text']");
    if (input == undefined) {
        input = $('#data-grid-filters input[type=\"text\"]:first');
    }
    if (input != undefined && input.length > 0) {
        input.focus().delay(1000).val(input.val());
        document.getElementById(input.attr('id')).setSelectionRange(100, 100);
    }
}

function convertToSlug(Text) {
    Text = cyrlat(Text.toLowerCase());
    return Text
        .replace(/[^\w _\-]+/g, '')
        .replace(/ +/g, '-');
}

function convertToCyrill(Text) {
    Text = latcyr(Text);
    return Text;
}

function latcyr(text) {
    text = text.replace(/Yu/g, "Ю");
    text = text.replace(/yu/g, "ю");
    text = text.replace(/Ya/g, "Я");
    text = text.replace(/ya/g, "я");
    text = text.replace(/Ch/g, "Ч");
    text = text.replace(/ch/g, "ч");
    text = text.replace(/Sh/g, "Ш");
    text = text.replace(/sh/g, "ш");
    text = text.replace(/Sh/g, "Щ");
    text = text.replace(/sh/g, "щ");
    text = text.replace(/Yo/g, "Ё");
    text = text.replace(/yo/g, "ё");
    text = text.replace(/G`/g, "Ғ");
    text = text.replace(/g`/g, "ғ");
    text = text.replace(/G'/g, "Ғ");
    text = text.replace(/g'/g, "ғ");
    text = text.replace(/O`/g, "Ў");
    text = text.replace(/o`/g, "ў");
    text = text.replace(/O'/g, "Ў");
    text = text.replace(/o'/g, "ў");
    text = text.replace(/A/g, "А");
    text = text.replace(/a/g, "а");
    text = text.replace(/B/g, "Б");
    text = text.replace(/b/g, "б");
    text = text.replace(/V/g, "В");
    text = text.replace(/v/g, "в");
    text = text.replace(/G/g, "Г");
    text = text.replace(/g/g, "г");
    text = text.replace(/D/g, "Д");
    text = text.replace(/d/g, "д");
    text = text.replace(/E/g, "Е");
    text = text.replace(/e/g, "е");
    text = text.replace(/J/g, "Ж");
    text = text.replace(/j/g, "ж");
    text = text.replace(/Z/g, "З");
    text = text.replace(/z/g, "з");
    text = text.replace(/I/g, "И");
    text = text.replace(/i/g, "и");
    text = text.replace(/Y/g, "Й");
    text = text.replace(/y/g, "й");
    text = text.replace(/K/g, "К");
    text = text.replace(/k/g, "к");
    text = text.replace(/L/g, "Л");
    text = text.replace(/l/g, "л");
    text = text.replace(/M/g, "М");
    text = text.replace(/m/g, "м");
    text = text.replace(/N/g, "Н");
    text = text.replace(/n/g, "н");
    text = text.replace(/O/g, "О");
    text = text.replace(/o/g, "о");
    text = text.replace(/P/g, "П");
    text = text.replace(/p/g, "п");
    text = text.replace(/R/g, "Р");
    text = text.replace(/r/g, "р");
    text = text.replace(/S/g, "С");
    text = text.replace(/s/g, "с");
    text = text.replace(/T/g, "Т");
    text = text.replace(/t/g, "т");
    text = text.replace(/U/g, "У");
    text = text.replace(/u/g, "у");
    text = text.replace(/F/g, "Ф");
    text = text.replace(/f/g, "ф");
    text = text.replace(/X/g, "Х");
    text = text.replace(/x/g, "х");
    text = text.replace(/C/g, "Ц");
    text = text.replace(/c/g, "ц");
    text = text.replace(/E/g, "Э");
    text = text.replace(/e/g, "э");
    text = text.replace(/H/g, "Ҳ");
    text = text.replace(/h/g, "ҳ");
    text = text.replace(/Q/g, "Қ");
    text = text.replace(/q/g, "қ");
    text = text.replace(/[`']/g, "ъ");

    return text;
}

function convertToLatin(Text) {
    Text = cyrlat(Text);
    return Text;
}

function cyrlat(text) {
    text = text.replace(/Ю/g, "Yu");
    text = text.replace(/ю/g, "yu");
    text = text.replace(/юе/g, "yuye");
    text = text.replace(/Я/g, "Ya");
    text = text.replace(/я/g, "ya");
    text = text.replace(/Ч/g, "Ch");
    text = text.replace(/ч/g, "ch");
    text = text.replace(/Ш/g, "Sh");
    text = text.replace(/ш/g, "sh");
    text = text.replace(/Щ/g, "Sh");
    text = text.replace(/щ/g, "sh");
    text = text.replace(/Ё/g, "Yo");
    text = text.replace(/ёе/g, "yoye");
    text = text.replace(/ё/g, "yo");
    text = text.replace(/Ғ/g, "G'");
    text = text.replace(/ғ/g, "g'");
    text = text.replace(/Ў/g, "O'");
    text = text.replace(/ў/g, "o'");
    text = text.replace(/А/g, "A");
    text = text.replace(/а/g, "a");
    text = text.replace(/ае/g, "aye");
    text = text.replace(/Б/g, "B");
    text = text.replace(/б/g, "b");
    text = text.replace(/В/g, "V");
    text = text.replace(/в/g, "v");
    text = text.replace(/Г/g, "G");
    text = text.replace(/г/g, "g");
    text = text.replace(/Д/g, "D");
    text = text.replace(/д/g, "d");
    text = text.replace(/Е/g, "E");
    text = text.replace(/е/g, "e");
    text = text.replace(/Ж/g, "J");
    text = text.replace(/ж/g, "j");
    text = text.replace(/З/g, "Z");
    text = text.replace(/з/g, "z");
    text = text.replace(/И/g, "I");
    text = text.replace(/и/g, "i");
    text = text.replace(/ие/g, "iye");
    text = text.replace(/Й/g, "Y");
    text = text.replace(/й/g, "y");
    text = text.replace(/К/g, "K");
    text = text.replace(/к/g, "k");
    text = text.replace(/Л/g, "L");
    text = text.replace(/л/g, "l");
    text = text.replace(/М/g, "M");
    text = text.replace(/м/g, "m");
    text = text.replace(/Н/g, "N");
    text = text.replace(/н/g, "n");
    text = text.replace(/О/g, "O");
    text = text.replace(/о/g, "o");
    text = text.replace(/ое/g, "oye");
    text = text.replace(/П/g, "P");
    text = text.replace(/п/g, "p");
    text = text.replace(/Р/g, "R");
    text = text.replace(/р/g, "r");
    text = text.replace(/С/g, "S");
    text = text.replace(/с/g, "s");
    text = text.replace(/Т/g, "T");
    text = text.replace(/т/g, "t");
    text = text.replace(/У/g, "U");
    text = text.replace(/у/g, "u");
    text = text.replace(/уе/g, "uye");
    text = text.replace(/Ф/g, "F");
    text = text.replace(/ф/g, "f");
    text = text.replace(/Х/g, "X");
    text = text.replace(/х/g, "x");
    text = text.replace(/Ц/g, "C");
    text = text.replace(/ц/g, "c");
    text = text.replace(/Э/g, "E");
    text = text.replace(/э/g, "e");
    text = text.replace(/Ҳ/g, "H");
    text = text.replace(/ҳ/g, "h");
    text = text.replace(/Қ/g, "Q");
    text = text.replace(/қ/g, "q");

    return text;
}

var decodeEntities = (function () {
    // this prevents any overhead from creating the object each time
    var element = document.createElement('div');

    function decodeHTMLEntities(str) {
        if (str && typeof str === 'string') {
            // strip script/html tags
            str = str.replace(/<script[^>]*>([\S\s]*?)<\/script>/gmi, '');
            str = str.replace(/<\/?\w(?:[^"'>]|"[^"]*"|'[^']*')*>/gmi, '');
            element.innerHTML = str;
            str = element.textContent;
            element.textContent = '';
        }

        return str;
    }

    return decodeHTMLEntities;
})();
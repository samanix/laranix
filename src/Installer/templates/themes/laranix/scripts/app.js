$('div#main-menu a').each(function () {

    let cur_path = window.location.pathname.split('/')[1],
        a_path = $(this)[0].pathname.split('/')[1];

    if (cur_path === a_path) {
        $(this).addClass('active').removeAttr('href');
        return false;
    }
});

$('div#main-menu .dropdown').removeClass('simple').dropdown();


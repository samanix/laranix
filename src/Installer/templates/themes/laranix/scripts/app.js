$('div#main-menu a').each(function () {
    if ($(this)[0].pathname == window.location.pathname) {
        $(this).addClass('active').removeAttr('href');
        return false;
    }
});

$('div#account-dropdown').removeClass('simple').dropdown();

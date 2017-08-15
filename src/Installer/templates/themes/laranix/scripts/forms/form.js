let form_id, form_validation_fields;


window.addEventListener('DOMContentLoaded', function() {
    let $_form = $('form#' + form_id + '-form'),
        $_form_inputs = $_form.find(':input'),
        _form_has_checkboxes = false;

    $_form_inputs.each(function () {
        $(this).removeAttr('required minlength maxlength');

        if ($(this).is(':checkbox')) {
            $(this).addClass('hidden');
            _form_has_checkboxes = true;
        }
    });

    if (_form_has_checkboxes) {
        $('.ui.checkbox').checkbox();
    }

    if (_recaptcha_enabled) {
        grecaptcha.render(form_id + '-recaptcha-render', {
            sitekey: '6Lc88CwUAAAAAINt_FlOLiDMi0hK5CBFDhEh5MsV',
            size: 'invisible',
            callback: function (token) {
                document.getElementById(form_id + '-form').submit();
            }
        });
    }

    let _form_validation = {
        inline: true,
        on: 'blur',
        onSuccess: function (e, validation_fields) {
            e.preventDefault();
            grecaptcha.execute();
        }
    };

    _form_validation.fields = form_validation_fields;

    $_form.form(_form_validation);
});

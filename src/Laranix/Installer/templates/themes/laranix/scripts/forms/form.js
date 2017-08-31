let formId, formValidationFields;

window.addEventListener('load', function() {
    let $_form = $('form#' + formId + '-form'),
        $_formInputs = $_form.find(':input'),
        _formHasCheckboxes = false;

    $_formInputs.each(function () {
        $(this).removeAttr('required minlength maxlength');

        if ($(this).is(':checkbox')) {
            $(this).addClass('hidden');
            _formHasCheckboxes = true;
        }
    });

    if (_formHasCheckboxes) {
        $('.ui.checkbox').checkbox();
    }

    let _recaptchaElement = $_form + '-recaptcha-render',
        _recaptcha_enabled = document.getElementById(_recaptchaElement);

    if (_recaptcha_enabled !== null) {
        grecaptcha.render(formId + '-recaptcha-render', {
            size: 'invisible',
            callback: function (token) {
                document.getElementById(formId + '-form').submit();
            }
        });
    }

    let _form_validation = {
        inline: true,
        on: 'blur',
        onSuccess: function (e, validation_fields) {
            if (_recaptcha_enabled !== null) {
                e.preventDefault();
                grecaptcha.execute();
            }
        }
    };

    _form_validation.fields = formValidationFields;

    $_form.form(_form_validation);
});

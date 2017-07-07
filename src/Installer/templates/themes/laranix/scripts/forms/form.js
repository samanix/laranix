let form_name, validation_fields,
    $_form, $_form_button, $_recaptchaDone, $_recaptchaField,
    $_has_checkboxes = false;

function recaptcha_callback() {
    $_recaptchaDone = true;
    document.getElementById('recaptcha-error').innerHTML = '';
}

$(document).ready(function () {
    $_form = $('form#' + form_name + '-form');
    $_form_button = $('button#submit-' + form_name + '-form');
    $_recaptchaDone = true;
    $_recaptchaField = document.getElementById('recaptcha-field');

    if ($_recaptchaField !== null) {
        $_recaptchaField.style.display = 'block';
        $_recaptchaDone = false;
    }

    $_form_inputs = $_form.find(':input');

    $_form_inputs.each(function () {
        $(this).removeAttr('required minlength maxlength');

        if ($(this).is(':checkbox')) {
            $(this).addClass('hidden');
            $_has_checkboxes = true;
        }
    });

    if ($_has_checkboxes) {
        $('.ui.checkbox').checkbox();
    }

    validate_form();
});

// Validate the form
function validate_form() {
    let form_validation = {
        inline: true,
        on: 'blur',
        onSuccess: function (e, validation_fields) {
            if (!$_recaptchaDone) {
                e.preventDefault();
                $_form.form('add errors', ['Please complete the captcha']);

                return false;
            }

            $_form_button.prop('disabled', true);
        }
    };

    form_validation.fields = validation_fields;

    $_form.form(form_validation);
}

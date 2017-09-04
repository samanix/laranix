formId = 'register';

formValidationFields = {
    fname: {
        identifier: 'first_name',
        rules: [
            {
                type: 'empty',
                prompt: 'Please enter your first name'
            },
            {
                type: 'maxLength[64]',
                prompt: 'First name cannot be longer than 64 characters'
            }
        ]
    },
    lastname: {
        identifier: 'last_name',
        rules: [
            {
                type: 'empty',
                prompt: 'Please enter your last name'
            },
            {
                type: 'maxLength[64]',
                prompt: 'Last name cannot be longer than 64 characters'
            }
        ]
    },
    email: {
        identifier: 'email',
        rules: [
            {
                type: 'email',
                prompt: 'Please enter a valid email'
            },
            {
                type: 'maxLength[255]',
                prompt: 'Email cannot be longer than 255 characters'
            }
        ]
    },
    email_c: {
        identifier: 'email_confirmation',
        rules: [
            {
                type: 'match[email]',
                prompt: 'Email does not match'
            }
        ]
    },
    company: {
        identifier: 'company',
        rules: [
            {
                type: 'maxLength[64]',
                prompt: 'Company cannot be longer than 64 characters'
            }
        ]
    },
    username: {
        identifier: 'username',
        rules: [
            {
                type: 'regExp[/^[A-Za-z0-9_-]{3,64}$/]',
                prompt: 'Please enter a valid username.'
            },
        ]
    },
    password: {
        identifier: 'password',
        rules: [
            {
                type: 'minLength[6]',
                prompt: 'Password must be at least 6 characters'
            }
        ]
    },
    password_c: {
        identifier: 'password_confirmation',
        rules: [
            {
                type: 'match[password]',
                prompt: 'Passwords do not match'
            }
        ]
    },
    terms_accept: {
        identifier: 'terms',
        rules: [
            {
                type: 'checked',
                prompt: 'You must accept the terms if you wish to register'
            }
        ]
    }
};

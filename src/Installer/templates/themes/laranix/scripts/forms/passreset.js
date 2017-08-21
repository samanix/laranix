formId = 'pass-reset';

formValidationFields = {
    token: {
        identifier: 'token',
        rules: [
            {
                type: 'regExp[/^[A-Fa-f0-9]{64}$/]',
                prompt: 'Invalid token'
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
    }
};

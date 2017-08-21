formId = 'login';

formValidationFields = {
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
    }
};

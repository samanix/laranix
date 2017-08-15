form_id = 'pass-forgot';

validation_fields = {
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
    }
};

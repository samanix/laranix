<?php
namespace __UserAppNamespace__Http\Requests\Auth\Email\Verification;

use Laranix\Support\Request\RequestValidator;

class PostVerify extends RequestValidator
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'token'     => 'required|regex:/^[A-Fa-f0-9]{64}$/',
            'email'     => 'required|email|max:255',
        ];
    }

    /**
     * Custom messages
     *
     * @return array
     */
    public function messages()
    {
        return [
            'token.regex'   => 'Invalid token',
        ];
    }
}

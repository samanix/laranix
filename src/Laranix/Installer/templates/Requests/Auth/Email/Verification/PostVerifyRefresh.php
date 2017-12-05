<?php
namespace __UserAppNamespace__Http\Requests\Auth\Email\Verification;

use Laranix\Support\Request\RequestValidator;

class PostVerifyRefresh extends RequestValidator
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email'     => 'required|email|max:255',
        ];
    }
}

<?php
namespace Laranix\Support\Request;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\RedirectResponse;

abstract class RequestValidator extends FormRequest
{
    /**
     * @var string
     */
    protected $fragment = 'form-errors';

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function getRedirectUrl()
    {
        $url = $this->redirector->getUrlGenerator();

        if ($this->redirect) {
            return $url->to($this->redirect);
        } elseif ($this->redirectRoute) {
            return $url->route($this->redirectRoute);
        } elseif ($this->redirectAction) {
            return $url->action($this->redirectAction);
        }

        return $url->previous() . (!empty($this->fragment) ? '#' . $this->fragment : '');
    }

    /**
     * @param mixed $errors
     * @return \Illuminate\Http\RedirectResponse
     */
    public function failedWithOtherReason($errors = 'There was an error when submitting the form.'): RedirectResponse
    {
        return $this->redirector
            ->to($this->getRedirectUrl())
            ->withInput()
            ->withErrors(is_array($errors) ? $errors : [$errors]);
    }
}

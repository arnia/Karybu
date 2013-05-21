<?php

namespace Karybu\Validator;

class ValidatorSession
{
    /** @var \ContextInstance */
    private $context;

    public function __construct(\ContextInstance $context = null)
    {
        if($context == null) {
            $context = \Context::getInstance();
        }

        $this->context = $context;
    }

    /**
     * clear error message to Session.
     * @return void
     **/
    function clear()
    {
        $_SESSION['XE_VALIDATOR_ERROR'] = '';
        $_SESSION['XE_VALIDATOR_MESSAGE'] = '';
        $_SESSION['XE_VALIDATOR_MESSAGE_TYPE'] = '';
        $_SESSION['XE_VALIDATOR_RETURN_URL'] = '';
    }

    /**
     * Save error message in SESSION in order to display after redirect (flash message)
     *
     * @param $code
     * @param $message
     * @param string $message_type
     * @param null $return_url
     */
    function saveError($code, $message, $message_type = 'error', $return_url = null)
    {
        if ($return_url == null) {
            $return_url = $this->context->get('error_return_url');
        }

        $_SESSION['XE_VALIDATOR_ERROR'] = $code;
        $_SESSION['XE_VALIDATOR_MESSAGE'] = $message;
        $_SESSION['XE_VALIDATOR_MESSAGE_TYPE'] = $message_type;
        $_SESSION['XE_VALIDATOR_RETURN_URL'] = $return_url;
    }

    /**
     * occurred error when, set input values to session.
     * @return void
     */
    function saveRequestVariables()
    {
        $requestVars = $this->context->getRequestVars();
        unset($requestVars->act, $requestVars->mid, $requestVars->vid, $requestVars->success_return_url, $requestVars->error_return_url);
        foreach ($requestVars AS $key => $value) {
            $_SESSION['INPUT_ERROR'][$key] = $value;
        }
    }

    /**
     * set error message to Session.
     * @return void
     **/
    function setErrorsToContext()
    {
        if (!empty($_SESSION['XE_VALIDATOR_ERROR']) && !$this->context->get('XE_VALIDATOR_ERROR')) {
            $this->context->set(
                'XE_VALIDATOR_ERROR',
                $_SESSION['XE_VALIDATOR_ERROR']
            );
        }
        if (!empty($_SESSION['XE_VALIDATOR_MESSAGE']) && !$this->context->get('XE_VALIDATOR_MESSAGE')) {
            $this->context->set(
                'XE_VALIDATOR_MESSAGE',
                $_SESSION['XE_VALIDATOR_MESSAGE']
            );
        }
        if (!empty($_SESSION['XE_VALIDATOR_MESSAGE_TYPE']) && !$this->context->get(
            'XE_VALIDATOR_MESSAGE_TYPE'
        )
        ) {
            $this->context->set('XE_VALIDATOR_MESSAGE_TYPE', $_SESSION['XE_VALIDATOR_MESSAGE_TYPE']);
        }
        if (!empty($_SESSION['XE_VALIDATOR_RETURN_URL']) && !$this->context->get(
            'XE_VALIDATOR_RETURN_URL'
        )
        ) {
            $this->context->set('XE_VALIDATOR_RETURN_URL', $_SESSION['XE_VALIDATOR_RETURN_URL']);
        }

        $this->clear();
    }

    /**
     * Used in the member module, where users can add custom fields
     */
    public function setupCustomErrorMessages()
    {
        if (!empty($_SESSION['XE_VALIDATOR_ERROR_LANG'])) {
            $errorLang = $_SESSION['XE_VALIDATOR_ERROR_LANG'];
            foreach ($errorLang as $key => $val) {
                $this->context->setLang($key, $val);
            }
            unset($_SESSION['XE_VALIDATOR_ERROR_LANG']);
        }
    }
}
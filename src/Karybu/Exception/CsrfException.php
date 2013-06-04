<?php
namespace Karybu\Exception;
class CsrfException extends \Exception {
    public function __construct(){
        return parent::__construct('msg_invalid_form_key');
    }
}
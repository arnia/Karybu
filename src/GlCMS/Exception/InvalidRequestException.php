<?php

namespace GlCMS\Exception;

class InvalidRequestException extends \Exception
{
    public function __construct() {
        return parent::__construct('msg_invalid_request');
    }
}
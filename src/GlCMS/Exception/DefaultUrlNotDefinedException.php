<?php

namespace GlCMS\Exception;

class DefaultUrlNotDefinedException extends \Exception
{
    public function __construct() {
        return parent::__construct('msg_default_url_is_not_defined');
    }
}
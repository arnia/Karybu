<?php

namespace Karybu\Exception;

class ModuleDoesNotExistException extends \Exception
{
    public function __construct() {
        return parent::__construct('msg_module_is_not_exists');
    }
}
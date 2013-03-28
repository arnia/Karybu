<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Razvan G Nutu
 * Date: 3/28/13
 * Time: 10:49 AM
 * To change this template use File | Settings | File Templates.
 */

namespace Karybu\Exception;


class DBConnectionFailedException extends \Exception{
    public function __construct($message = "msg_database_connection_failed", $code = 0, \Exception $previousException = null) {
        return parent::__construct($message, $code, $previousException);
    }

}
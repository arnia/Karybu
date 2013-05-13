<?php
require_once __DIR__ . "/../vendor/autoload.php";
require_once 'MockHelper.php';
require_once 'classes/object/Object.mock.php';

if(!defined('__DEBUG__')) define('__DEBUG__', 1);
if(!defined('__XE__')) define('__XE__', true);
if(!defined('__ZBXE__')) define('__ZBXE__', true);
if(!defined('_KARYBU_PATH_')) define('_KARYBU_PATH_', realpath(dirname(__FILE__).'/../').'/');

$_SERVER['SCRIPT_NAME'] = '/xe/index.php';
error_reporting(-1);

/**
 * Print out the message
 **/
function _log($msg) {
	$args = func_get_args();

	foreach($args as $arg) {
		fwrite(STDOUT, "\n");
		fwrite(STDOUT, print_r($arg, true));
	}
		fwrite(STDOUT, "\n");
}

/* End of file Bootstrap.php */
/* Location: ./tests/Bootstrap.php */

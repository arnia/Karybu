<?php
use Symfony\Component\HttpFoundation\Request;
use Karybu\HttpKernel\Kernel;

if(!defined('KARYBU_ENVIRONMENT')) exit();

$isCommandLine = ( php_sapi_name() == 'cli' );

/**
 * Declare constants for generic use and for checking to avoid a direct call from the Web
 **/
define('__XE__',   true);
define('__ZBXE__', true); // deprecated : __ZBXE__ will be removed. Use __XE__ instead.

require dirname(__FILE__) . '/config/config.inc.php';

$validCommandLineCall = $isCommandLine && isset($argv[1]) && filter_var($argv[1], FILTER_VALIDATE_URL);

//create request using first call parameter if the script is called from the console with a valid url as first param
$request = $validCommandLineCall ? Request::create($argv[1]) : Request::createFromGlobals();

$kernel = new Kernel('prod', false);

$response = $kernel->handle($request);
$response->prepare($request);
$response->send();

$kernel->terminate($request, $response);
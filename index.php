<?php
use Symfony\Component\HttpFoundation\Request;
use Karybu\HttpKernel\Kernel;
use Karybu\Environment\Environment;
require_once 'src/Karybu/Environment/Environment.php';
//set default environment
$envCode = Environment::DEFAULT_ENVIRONMENT;
//check if environment file exists
$filename = 'files/config/environment.txt';
if (file_exists($filename)) {
    $envCode = file_get_contents($filename);
}
//get the current valid environment
$env = Environment::getEnvironment($envCode, true);
define('KARYBU_ENVIRONMENT', $env['code']);
if ($env['dev_mode']) {
    /**
     * dev protection
     */
    /*if (isset($_SERVER['HTTP_CLIENT_IP']) || isset($_SERVER['HTTP_X_FORWARDED_FOR']) || !in_array(@$_SERVER['REMOTE_ADDR'], array('127.0.0.1', 'fe80::1', '::1'))) {
        header('HTTP/1.0 403 Forbidden');
        exit('You are not allowed to access this resource. Check '.basename(__FILE__).' for more information.');
    }*/
}
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

$kernel = new Kernel($env['code'], $env['dev_mode']);

$response = $kernel->handle($request);
$response->prepare($request);
$response->send();

$kernel->terminate($request, $response);
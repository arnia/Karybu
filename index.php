<?php
require_once 'src/Karybu/Environment/Environment.php';
$filename = 'files/config/environment.txt';
if (file_exists($filename)) {
    $content = file_get_contents($filename);
    $env = \Karybu\Environment\Environment::getEnvironment($content);
    if (count($env) > 0) {
        define('KARYBU_ENVIRONMENT', $content);
    }
    else {
        define('KARYBU_ENVIRONMENT', \Karybu\Environment\Environment::DEFAULT_ENVIRONMENT);
    }
}
else{
    define('KARYBU_ENVIRONMENT', \Karybu\Environment\Environment::DEFAULT_ENVIRONMENT);
}
require "index_" . KARYBU_ENVIRONMENT . ".php";
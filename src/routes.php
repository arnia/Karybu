<?php
/**
 * This file returns the core route collection
 * Currently it resides in config/routes.yml
 */

use Symfony\Component\Routing;
use Symfony\Component\Config;

$configPaths = array(__DIR__ . '/../config');
$locator = new Config\FileLocator($configPaths);
$loader = new Routing\Loader\YamlFileLoader($locator);
$routes = $loader->load('routes.yml');

return $routes;
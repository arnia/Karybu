<?php
/**
 * This file returns the core route collection
 * Currently it resides in config/routes.yml
 */

use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;

$configPaths = array(__DIR__ . '/../config');
$locator = new FileLocator($configPaths);
$loader = new YamlFileLoader($locator);
$routes = $loader->load('routes.yml');

return $routes;
<?php
// florin, 3/4/13, 12:55 PM
namespace Karybu\DependencyInjection\Module;

use ReflectionClass;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Extension\Extension as SymfonyExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * base class for module extensions
 * also checks for a configuration class
 * children should implement the load and getAlias (module name) methods
 */
abstract class Extension extends SymfonyExtension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $reflector = new ReflectionClass($this);
        $path = dirname($reflector->getFileName());
        $loader = new YamlFileLoader(
            $container,
            new FileLocator($path . '/../conf')
        );
        $loader->load('services.yml');
    }
}
<?php
// florin, 3/4/13, 12:55 PM
namespace Karybu\DependencyInjection\Module;

use ReflectionClass;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * base class for module extensions
 * also checks for a configuration class
 * children should implement the load and getAlias (module name) methods
 */
abstract class Extension implements ExtensionInterface
{
    public function getXsdValidationBasePath()
    {
        return false;
    }

    public function getNamespace()
    {
        return 'http://example.org/schema/dic/'.$this->getAlias();
    }

    /**
     * http://symfony.com/doc/2.0/cookbook/bundles/extension.html
     */
    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        $reflected = new ReflectionClass($this);
        $namespace = $reflected->getNamespaceName();
        $class = $namespace . '\\Configuration';
        if (class_exists($class)) {
            if (!method_exists($class, '__construct')) {
                $configuration = new $class();
                return $configuration;
            }
        }
        return null;
    }

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
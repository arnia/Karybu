<?php
// florin, 3/26/13, 2:52 PM

namespace Karybu\Module\Debug\DependencyInjection;

use Karybu\DependencyInjection\Module\Extension;
use Karybu\Module\Debug\EventListener\DebugToolbarListener;
use ReflectionClass;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class DebugExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        // parent::load($configs, $container);

        $debug = (boolean)$container->getParameter('kernel.debug');

        $reflector = new ReflectionClass($this);
        $path = dirname($reflector->getFileName());
        $loader = new YamlFileLoader(
            $container,
            new FileLocator($path . '/../conf')
        );
        $config_filename = 'services_debug_' . ($debug ? 'on' : 'off') . '.yml';
        $loader->load($config_filename);

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);
        $this->setContainerParameters($config, $container);
    }

    protected function setContainerParameters(array $sanitizedConfig, ContainerBuilder $container)
    {
        $container->setParameter('logger.slow_queries_threshold', $sanitizedConfig['slow_queries_threshold']);
        $toolbarMode = $sanitizedConfig['toolbar'] ? DebugToolbarListener::ENABLED : DebugToolbarListener::DISABLED;
        $container->setParameter('logger.debug.toolbar', $toolbarMode);
        $container->setParameter('logger.slow_queries_threshold', $sanitizedConfig['slow_queries_threshold']);
        $container->setParameter('logger.debug.level', $sanitizedConfig['level']);
    }
}
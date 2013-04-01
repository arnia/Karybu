<?php
// florin, 3/26/13, 2:52 PM

namespace Karybu\Module\Debug\DependencyInjection;

use Karybu\DependencyInjection\Module\Extension;
use Karybu\Module\Debug\EventListener\DebugToolbarListener;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DebugExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        parent::load($configs, $container);
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);
        $this->setContainerParameters($config, $container);
    }

    protected function setContainerParameters(array $sanitizedConfig, ContainerBuilder $container)
    {
        $container->setParameter('logger.slow_queries_threshold', $sanitizedConfig['slow_queries_threshold']);
        $toolbarMode = $sanitizedConfig['toolbar'] ? DebugToolbarListener::ENABLED : DebugToolbarListener::DISABLED;
        $container->setParameter('logger.debug.toolbar', $toolbarMode);
    }
}
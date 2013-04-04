<?php

namespace Karybu\Bundle\Core;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class KarybuCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('dispatcher')) {
            return;
        }

        $definition = $container->getDefinition(
            'dispatcher'
        );

        $taggedServices = $container->findTaggedServiceIds(
            'cms.event_listener'
        );
        foreach ($taggedServices as $id => $attributes) {
            $definition->addMethodCall(
                'addSubscriber',
                array(new Reference($id))
            );
        }
    }
}
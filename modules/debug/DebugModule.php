<?php
namespace GlCMS\Module\Debug;

use GlCMS\HttpKernel\Module;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DebugModule extends Module
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
    }
}

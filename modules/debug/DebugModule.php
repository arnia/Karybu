<?php
namespace Karybu\Module\Debug;

use Karybu\HttpKernel\Module;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DebugModule extends Module
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
    }
}

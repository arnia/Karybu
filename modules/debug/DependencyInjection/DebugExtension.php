<?php
// florin, 3/26/13, 2:52 PM

namespace Karybu\Module\Debug\DependencyInjection;

use Karybu\DependencyInjection\Module\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DebugExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        /**
         * loads ../conf/services.yml (code in parent class)
         */
        parent::load($configs, $container);
    }

    public function getAlias()
    {
        return 'debug';
    }
}
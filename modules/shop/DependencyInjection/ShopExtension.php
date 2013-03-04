<?php
// florin, 3/4/13, 12:55 PM
namespace GlCMS\Module\Shop\DependencyInjection;

use GlCMS\DependencyInjection\Module\Extension as CmsExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Config\FileLocator;

class ShopExtension extends CmsExtension
{
    public function getAlias()
    {
        return 'shop';
    }

    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );
        $loader->load('services.xml');
    }
}
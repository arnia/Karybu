<?php
// florin, 2/26/13, 5:10 PM
namespace GlCMS\Module\Shop\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

/**
 * http://symfony.com/doc/current/cookbook/bundles/extension.html#creating-an-extension-class
 */
class ShopExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        /**
         * Make sure the above merging technique makes sense for your bundle. This is just an example, and you should be careful to not use it blindly.
         */
        $config = array();
        foreach ($configs as $subConfig) {
            $config = array_merge($config, $subConfig);
        }

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $config);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        if (isset($config['enabled']) && $config['enabled']) {
            $loader->load('services.xml');
        }
    }

    public function getAlias()
    {
        return 'shop';
    }
}

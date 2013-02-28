<?php
// florin, 2/27/13, 12:48 PM
namespace GlCMS\Module\Shop\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('shop');

        $rootNode
            ->children()
            ->booleanNode('caca')->defaultFalse()->end()
            ->end();

        return $treeBuilder;
    }
}
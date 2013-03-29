<?php
// florin, 3/27/13, 3:30 PM
namespace Karybu\Module\Debug\DependencyInjection;

use Monolog\Logger;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**

debug:{
    enabled:true ->gzip:off
    toolbar:false
    slow_query : 1000 ms
    channels:[file,chrome,firebug]
    filter_ip:127.0.0.1
}

 * Class Configuration
 * @package Karybu\Module\Debug\DependencyInjection\Configuration
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('debug');
        $rootNode
            ->children()
                ->booleanNode('toolbar')->defaultTrue()->info('show the toolbar or not?')->end()
                ->integerNode('slow_queries_threshold')->defaultValue(800)->min(0)->end()
                ->integerNode('level')->defaultValue(Logger::DEBUG)->min(0)->end()
                ->arrayNode('channels')
                    ->example(array('chrome'))
                    ->beforeNormalization()
                        ->ifTrue(function($v) { return !is_array($v); })
                        ->then(function($v) { return array($v); })
                    ->end()
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('allowed_ips')
                    ->example(array('127.0.0.1', '127.0.0.2'))
                    ->beforeNormalization()
                        ->ifTrue(function($v) { return !is_array($v); })
                        ->then(function($v) { return array($v); })
                    ->end()
                    ->prototype('scalar')->end()
                ->end()
            ->end();
        return $treeBuilder;
    }
}
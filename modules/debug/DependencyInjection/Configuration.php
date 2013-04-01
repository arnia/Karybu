<?php
// florin, 3/27/13, 3:30 PM
namespace Karybu\Module\Debug\DependencyInjection;

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

                ->scalarNode('level')
                    ->beforeNormalization()
                        ->ifTrue(function($v){ return !defined('Monolog\Logger::' . strtoupper($v)); })
                        ->thenInvalid('Invalid logging level %s')
                    ->end()
                    ->beforeNormalization()
                        ->ifInArray(array('debug', 'notice', 'warning', 'error', 'critical', 'alert', 'emergency'))
                        ->then(function($v) { return constant('Monolog\Logger::' . strtoupper($v)); })
                    ->end()
                    ->defaultValue('debug')
                ->end()

                ->arrayNode('handlers')
                    ->defaultValue(array('toolbar'))
                    ->validate()
                    ->ifTrue(function($v){ $a = array_diff($v, array('firebug', 'chrome', 'files')); return !empty($a); })
                        ->thenInvalid('Invalid logging handler %s')
                    ->end()
                    ->beforeNormalization()
                        ->ifString()
                        ->then(function($v) { return array($v); })
                    ->end()
                    ->prototype('scalar')->end()
                ->end()

            ->end();
        return $treeBuilder;
    }
}
<?php
// florin, 3/5/13, 5:09 PM
namespace Karybu\Routing;

use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\Router as SymfonyRouter;

use Symfony\Component\Config\Loader\LoaderInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;

class Router extends SymfonyRouter
{
    public function __construct(
        LoaderInterface $loader,
        RequestContext $context = null,
        LoggerInterface $logger = null,
        $debug = false
    ) {
        $options = array(
            'cache_dir' => _XE_PATH_ . 'files/cache',
            'debug' => (boolean)$debug,
            'generator_cache_class' => 'cmsUrlGeneratorCache',
            'matcher_cache_class' => 'cmsUrlMatcherCache'
        );
        parent::__construct($loader, 'routes.yml', $options, $context, $logger);
    }

    public function getMatcher()
    {
        if (null !== $this->matcher) {
            return $this->matcher;
        }

        if ($this->isFilesFolderAvailable()){
            return $this->getOrdinaryMatcher();
        } else {
            return $this->getNotCacheableMatcher();
        }
    }

    private function isFilesFolderAvailable(){
        return is_writable($this->rootDir . 'files');
    }

    private function getOrdinaryMatcher(){
        if (null === $this->options['cache_dir'] || null === $this->options['matcher_cache_class']) {
            return $this->matcher = new $this->options['matcher_class']($this->getRouteCollection(), $this->context);
        }

        $class = $this->options['matcher_cache_class'];
        $cache = new ConfigCache($this->options['cache_dir'].'/'.$class.'.php', $this->options['debug']);
        if (!$cache->isFresh($class)) {
            $dumper = new $this->options['matcher_dumper_class']($this->getRouteCollection());

            $options = array(
                'class'      => $class,
                'base_class' => $this->options['matcher_base_class'],
            );

            $cache->write($dumper->dump($options), $this->getRouteCollection()->getResources());
        }

        require_once $cache;

        return $this->matcher = new $class($this->context);
    }

    private function getNotCacheableMatcher(){
        $class = $this->options['matcher_class'];
        //return $this->matcher = new $class($this->context);
        return $this->matcher = new UrlMatcher($this->getRouteCollection(), $this->context);
    }

}
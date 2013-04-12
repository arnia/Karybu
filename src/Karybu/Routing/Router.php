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

        if ($this->isFilesFolderAvailable()) {
            return parent::getMatcher();
        } else {
            return $this->getNotCacheableMatcher();
        }
    }

    private function isFilesFolderAvailable()
    {
        return is_writable(_XE_PATH_ . 'files');
    }

    private function getNotCacheableMatcher()
    {
        $class = $this->options['matcher_class'];
        //return $this->matcher = new $class($this->context);
        return $this->matcher = new UrlMatcher($this->getRouteCollection(), $this->context);
    }

}
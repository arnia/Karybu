<?php
// florin, 3/4/13, 11:39 AM
namespace GlCMS\Routing;

use Symfony\Component\Routing\RouteCollection as SymfonyRouteCollection;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;

class RouteCollection extends SymfonyRouteCollection
{
    protected $routes;

    public function __construct()
    {
        $cmsRoutes = $this::getCMSRoutes();
        $this->addAll($cmsRoutes);
    }

    public static function getCMSRoutes($path='/config', $file='routes.yml')
    {
        $configPaths = array(_XE_PATH_ . $path);
        $locator = new FileLocator($configPaths);
        $loader = new YamlFileLoader($locator);
        $routes = $loader->load($file);
        return $routes;
    }

    public function addAll(SymfonyRouteCollection $coll)
    {
        foreach ($coll->all() as $name => $route) {
            $this->add($name, $route);
        }
    }
}

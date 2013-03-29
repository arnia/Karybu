<?php
// florin, 3/5/13, 7:13 PM
namespace Karybu\Config;

use Symfony\Component\HttpKernel\Config\FileLocator;

/**
 * Locator for (all) configs
 */
class ConfigLocator extends FileLocator
{
    protected $paths;

    public function __construct()
    {
        /*
         * main config dir
         */
        $coreRoutes = array(_XE_PATH_ . 'config');
        /*
         * module config dirs
         */
        $moduleRoutes = glob(_XE_PATH_ . 'modules/*/{Resources/config,conf}', GLOB_ONLYDIR | GLOB_BRACE);
        /*
         * adding all paths to be searched against routes.yml
         */
        $this->paths = array_merge($coreRoutes, $moduleRoutes);
    }

}
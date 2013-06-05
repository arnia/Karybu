<?php
namespace Karybu\Twig;

use Symfony\Component\HttpKernel\KernelInterface;

class Loader_Filesystem extends \Twig_Loader_Filesystem
{
    /**
     * Constructor.
     *
     * @param string|array $paths A path or an array of paths where to look for templates
     */
    public function __construct($kernelRootPath)
    {
        $paths = self::getModulePaths($kernelRootPath);
        parent::__construct($paths);
    }

    /**
     * @param KernelInterface $kernel
     * @return array Array of module paths
     */
    public static function getModulePaths($rootPath)
    {
        $paths = glob($rootPath . 'modules/*/{Resources/templates,tpl}', GLOB_ONLYDIR | GLOB_BRACE);
        return $paths;
    }

}
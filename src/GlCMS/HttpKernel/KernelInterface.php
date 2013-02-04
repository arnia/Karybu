<?php
// florin, 2/1/13, 1:53 PM

namespace GlCMS\HttpKernel;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Config\Loader\LoaderInterface;

interface KernelInterface extends HttpKernelInterface, \Serializable
{
    /**
     * Loads the container configuration
     *
     * @param LoaderInterface $loader A LoaderInterface instance
     */
    public function registerContainerConfiguration(LoaderInterface $loader);

    /**
     * Boots the current kernel.
     */
    public function boot();

    /**
     * Gets all module instances.
     *
     * @return array An array of module instances
     */
    public function getModules();

    /**
     * Checks if a given class name belongs to an active module.
     *
     * @param string $class A class name
     *
     * @return Boolean true if the class belongs to an active module, false otherwise
     */
    public function isClassInActiveBundle($class);

    /**
     * Returns a bundle and optionally its descendants by its name.
     *
     * @param string  $name  Bundle name
     * @param Boolean $first Whether to return the first bundle only or together with its descendants
     *
     * @return ModuleInterface|Array A BundleInterface instance or an array of BundleInterface instances if $first is false
     *
     * @throws \InvalidArgumentException when the bundle is not enabled
     */
    public function getModule($name, $first = true);

    /**
     * Returns the file path for a given resource.
     *
     * A Resource can be a file or a directory.
     *
     * The resource name must follow the following pattern:
     *
     *     @ModuleName/path/to/a/file.something
     *
     * Where the remaining part is the relative path in the module.
     *
     * If $dir is passed, and the first segment of the path is Resources,
     * this method will look for a file named:
     *
     *     $dir/ModuleName/path/without/Resources
     *
     * @param string  $name  A resource name to locate
     * @param string  $dir   A directory where to look for the resource first
     * @param Boolean $first Whether to return the first path or paths for all matching bundles
     *
     * @return string|array The absolute path of the resource or an array if $first is false
     *
     * @throws \InvalidArgumentException if the file cannot be found or the name is not valid
     * @throws \RuntimeException         if the name contains invalid/unsafe characters
     */
    public function locateResource($name, $dir = null, $first = true);

    /**
     * Gets the name of the kernel
     * @return string The kernel name
     */
    public function getName();

    /**
     * Checks if debug mode is enabled.
     * @return Boolean true if debug mode is enabled, false otherwise
     */
    public function isDebug();

    /**
     * Gets the application root dir.
     * @return string The application root dir
     */
    public function getRootDir();

    /**
     * Gets the current container.
     * @return ContainerInterface A ContainerInterface instance
     */
    public function getContainer();

    /**
     * Gets the request start time (not available if debug is disabled).
     * @return integer The request start timestamp
     */
    public function getStartTime();

    /**
     * Gets the cache directory.
     * @return string The cache directory
     */
    public function getCacheDir();

    /**
     * Gets the log directory.
     * @return string The log directory
     */
    public function getLogDir();

    /**
     * Gets the charset of the application.
     * @return string The charset
     */
    public function getCharset();

}
<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Karybu\Routing\Loader;

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Routing\Loader\YamlFileLoader as SymfonyYamlFileLoader;

class YamlFileLoader extends SymfonyYamlFileLoader
{
    /**
     * Modified to load multiple paths
     */
    public function load($file, $type = null)
    {
        $paths = $this->locator->locate($file, null, false);

        $collection = new RouteCollection();

        foreach ($paths as $path) {

            $subCollection = new RouteCollection();

            $config = Yaml::parse($path);

            $subCollection->addResource(new FileResource($path));

            // empty file
            if (null === $config) {
                continue;
            }

            // not an array
            if (!is_array($config)) {
                throw new \InvalidArgumentException(sprintf('The file "%s" must contain a YAML array.', $path));
            }

            foreach ($config as $name => $config) {
                if (isset($config['pattern'])) {
                    if (isset($config['path'])) {
                        throw new \InvalidArgumentException(sprintf('The file "%s" cannot define both a "path" and a "pattern" attribute. Use only "path".', $path));
                    }

                    $config['path'] = $config['pattern'];
                    unset($config['pattern']);
                }

                $this->validate($config, $name, $path);

                if (isset($config['resource'])) {
                    $this->parseImport($subCollection, $config, $path, $file);
                } else {
                    $this->parseRoute($subCollection, $name, $config, $path);
                }
            }

            $collection->addCollection($subCollection);

        }

        return $collection;
    }
}

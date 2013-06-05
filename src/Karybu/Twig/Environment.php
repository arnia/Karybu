<?php

namespace Karybu\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig_LoaderInterface;

/**
 * Loads template from the filesystem.
 */
class Environment extends \Twig_Environment
{
    /**
     * needed this in order to allow Twig to work with dhe DI container
     */
    public function setDebug($bool)
    {
        if ($bool) {
            $this->enableDebug();
            $this->enableAutoReload();
        }
        else $this->disableDebug();
    }

}

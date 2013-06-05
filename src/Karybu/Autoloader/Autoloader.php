<?php
// florin, 2/6/13, 5:19 PM
namespace Karybu\Autoloader;

class Autoloader
{
    /**
     * Constructor sets classes path
     *
     * When a class is not found, the spl_autoload_register
     * sends to the loader function below with $class set to the missing class name.
     */
    public function __construct()
    {
        spl_autoload_register(array($this, 'load'));
    }

    /**
     * Loader checks if the missing $class exists
     *
     * @param $class
     */
    public function load($class)
    {
        $compatLoader = new Loader\CompatLoader($class);
        if (!$compatLoader->isOk()) {
            $psrZeroLoader = new Loader\PsrLoader($class);
            if (!$psrZeroLoader->isOk()) {
                // die('niet: ' . $class);
                // autoloader failed, but let it continue the execution flow
                // (maybe the next autoloader will pick it up)
            }
        }

    }

}

<?php
// florin, 2/21/13, 7:29 PM

namespace GlCMS\Autoloader\Loader;

interface LoaderInterface
{
    /**
     * @param $className
     * @return mixed The result of the final include_once (0, 1 or the RETURNed value)
     */
    public function load($className);
}

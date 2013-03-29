<?php
namespace Karybu\HttpKernel;

use Symfony\Component\HttpKernel\HttpKernel as K;

class HttpKernel extends K
{
    protected $debug;

    public function setDebug($debug)
    {
        $this->debug = (boolean) $debug;
        return $this;
    }

    public function isDebug()
    {
        return (boolean) $this->debug;
    }

}
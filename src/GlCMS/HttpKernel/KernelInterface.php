<?php
// florin, 2/1/13, 1:53 PM

namespace GlCMS\HttpKernel;

use Symfony\Component\HttpKernel\KernelInterface as SymfonyKernelInterface;

interface KernelInterface extends SymfonyKernelInterface
{

    public function getModules();

    public function registerModules();

}
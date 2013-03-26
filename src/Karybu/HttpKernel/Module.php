<?php
// florin, 2/4/13, 4:00 PM
namespace Karybu\HttpKernel;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class Module extends Bundle
{

    public function getContainerExtension()
    {
        if (null === $this->extension) {
            $basename = preg_replace('/Module$/', '', $this->getName());
            $class = $this->getNamespace().'\\DependencyInjection\\'.$basename.'Extension';
            if (class_exists($class)) {
                /** @var $extension Extension */
                $extension = new $class();
                // check naming convention
                $expectedAlias = Container::underscore($basename);
                if ($expectedAlias != $extension->getAlias()) {
                    throw new \LogicException(sprintf(
                        'The extension alias for the default extension of a '.
                            'module must be the underscored version of the '.
                            'module name ("%s" instead of "%s")',
                        $expectedAlias, $extension->getAlias()
                    ));
                }
                $this->extension = $extension;
            } else {
                $this->extension = false;
            }
        }
        if ($this->extension) {
            return $this->extension;
        }
    }

}

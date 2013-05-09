<?php
namespace Karybu\DependencyInjection\Dummy;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class Extension implements ExtensionInterface
{
    protected $_namespace = 'dummy';
    public function getNamespace()
    {
        return $this->_namespace;
    }
    public function getXsdValidationBasePath()
    {
        return '';
    }

    public function getAlias()
    {
        return $this->_namespace;
    }
    public function load(array $configs, ContainerBuilder $configuration)
    {
        return '';
    }
    public function setNamespace($namespace){
        $this->_namespace = $namespace;
    }
}
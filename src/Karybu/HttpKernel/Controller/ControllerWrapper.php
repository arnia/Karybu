<?php
namespace Karybu\HttpKernel\Controller;

class ControllerWrapper
{
    private $oModule;

    function __construct(\ModuleObject $oModule)
    {
        $this->setModuleInstance($oModule);
    }

    function __invoke($arguments = array())
    {
        if (!$this->oModule->skipAct) {
            $output = call_user_func_array(array($this->oModule, $this->oModule->act), $arguments);
        }
        else {
            $output = null;
        }
        return array('output' => $output, 'oModule' => $this->oModule);
    }

    function getModuleInstance()
    {
        return $this->oModule;
    }

    function setModuleInstance(\ModuleObject $oModule)
    {
        $this->oModule = $oModule;
    }

}
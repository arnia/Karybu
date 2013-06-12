<?php
namespace Karybu\HttpKernel\Controller;

class ControllerWrapper
{
    /** @var \ModuleObject */
    private $oModule;

    function __construct(\ModuleObject $oModule)
    {
        $this->setModuleInstance($oModule);
    }

    function __invoke($arguments = array())
    {
        if (!isset($this->oModule->skipAct)) {
            $output = call_user_func_array(array($this->oModule, $this->oModule->act), $arguments);
        } else {
            $output = null;
        }
        return array('output' => $output, 'oModule' => $this->oModule);
    }

    public function getControllerParameters()
    {
        $act = $this->oModule->act;
        if (substr($act, 0, 4) == 'proc') {
            // don't get args from procs (the login won't work anymore, for instance)
            return array();
        }
        else {
            $r = new \ReflectionMethod($this->oModule, $act);
            return $r->getParameters();
        }
    }

    public function isError()
    {
        return $this->oModule->error === -1;
    }

    public function isProc()
    {
        return substr($this->oModule->act, 0, 4) == 'proc';
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
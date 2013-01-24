<?php
namespace GlCMS;

use Symfony\Component\Routing;
use Symfony\Component\HttpKernel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class Core extends HttpKernel\HttpKernel
{
    public $oModuleHandler;
    public $oContext;
    public $oModule;

//    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
//    {
//        $this->oContext = \Context::getInstance();
//        $this->oContext->init();
//        if ($this->oContext->checkSSO()) {
//            $this->oModuleHandler = new \ModuleHandler();
//            if ($this->oModuleHandler->init()) {
//                $this->oModule = $this->oModuleHandler->procModule();
//                ob_start();
//                $this->oModuleHandler->displayContent($this->oModule);
//                $content = ob_get_clean();
//                $response = parent::handle($request, $type, $catch);
//                $response->setContent($content);
//                return $response;
//            }
//        }
//        $this->oContext->close();
//    }
}
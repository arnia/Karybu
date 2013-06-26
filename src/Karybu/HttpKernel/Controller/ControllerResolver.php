<?php
namespace Karybu\HttpKernel\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\HttpKernel\Controller\ControllerResolver as BaseResolver;

require_once _KARYBU_PATH_ . 'classes/module/ModuleMatcher.class.php';

class ControllerResolver extends BaseResolver
{
    protected $container;
    protected $parser;

    public function getController(Request $request)
    {
        $oModuleModel = getModel('module');
        $error = $request->attributes->get('error');
        $module = $request->attributes->get('module');
        $act = $request->attributes->get('act');
        $is_mobile = $request->attributes->get('is_mobile');
        $is_installed = $request->attributes->get('is_installed');
        $module_info = $request->attributes->get('module_info');

        if ($error) {
            /** @var $oModule \ModuleObject */
            $module = $error->module;
            $module_matcher = new \ModuleMatcher();
            $kind = $module_matcher->getKind('dispMessage', $module);
            $error->module_key = new \ModuleKey($module, $module_info->module_type, $kind);
            $oModule = $error;
        } else if ($request->attributes->has('_controller')) {
            $controller = parent::getController($request);
            $oModule = $controller[0];
            $act = $controller[1];

            $xml_info = $oModuleModel->getModuleActionXml($module);
            $oModule->ruleset = $xml_info->action->{$act}->ruleset;

            $oModule->setAct($act);

            $module_info->module_type = $xml_info->action->{$act}->type;

            $module_matcher = new \ModuleMatcher();
            $kind = $module_matcher->getKind($act, $module);
            $oModule->module_key = new \ModuleKey($module, $module_info->module_type, $kind);

            $oModule->setModuleInfo($module_info, $xml_info);
        } else {
            $module_matcher = new \ModuleMatcher();
            $oModule = $module_matcher->getModuleInstance($act, $module, $oModuleModel, $is_mobile, $is_installed, $module_info);
        }

        if ($oModule instanceof ContainerAwareInterface) {
            $oModule->setContainer($this->container);
        }

        return new ControllerWrapper($oModule);
    }

    public function getArguments(Request $request, $controller)
    {
        if (is_array($controller)) {
            $r = new \ReflectionMethod($controller[0], $controller[1]);
            $parameters = $r->getParameters();
        } elseif (is_object($controller) && !$controller instanceof \Closure) {
            $r = new \ReflectionObject($controller);
            if ($controller instanceof ControllerWrapper && $r->hasMethod('getControllerParameters') && !$controller->isError() && !$controller->isProc()) {
                $parameters = $controller->getControllerParameters(); // mirroring it
            }
            else {
                $r = $r->getMethod('__invoke');
                $parameters = $r->getParameters();
            }
        } else {
            $r = new \ReflectionFunction($controller);
            $parameters = $r->getParameters();
        }

        return $this->doGetArguments($request, $controller, $parameters);
    }

    /**
     * Returns a callable for the given controller.
     *
     * @param string $controller A Controller string
     *
     * @return mixed A PHP callable
     */
    protected function createController($controller)
    {
        if (false === strpos($controller, '::')) {
            throw new \InvalidArgumentException(sprintf('Unable to find controller "%s".', $controller));
        }

        list($class, $method) = explode('::', $controller, 2);

        if (!class_exists($class)) {
            throw new \InvalidArgumentException(sprintf('Class "%s" does not exist.', $class));
        }

        preg_match_all('/((?:^|[A-Z])[a-z]+)/',$class,$matches);
        $count = count($matches[1]);
        if ($count == 2) {
            list($module, $type) = $matches[1];
        }
        elseif ($count == 3) {
            list($module, $kind, $type) = $matches[1];
        }
        else {
            throw new \InvalidArgumentException(sprintf('Invalid class name "%s".', $class));
        }

        require_once _KARYBU_PATH_ . 'classes/module/ModuleHandler.class.php';
        $oModule = \ModuleHandler::getModuleInstance($module, $type, $kind);

        return array($oModule, $method);
    }
}

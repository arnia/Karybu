<?php
namespace GlCMS;
use \Symfony\Component\HttpKernel\Controller;
use Symfony\Component\HttpFoundation\Request;

require_once _XE_PATH_ . 'classes/module/ModuleMatcher.class.php';

class ControllerResolver extends Controller\ControllerResolver
{

    public function getController(Request $request, $oModuleModel = null)
    {
        $module = $request->attributes->get('module');
        $act = $request->attributes->get('act');
        $is_mobile = $request->attributes->get('is_mobile');
        $is_installed = $request->attributes->get('is_installed');
        $module_info = $request->attributes->get('module_info');

        if ($request->attributes->has('_controller')) {
            if (is_array($include_paths = $request->attributes->get('_include'))) {
                foreach ($include_paths as $include_path) {
                    $include_path = _XE_PATH_ . $include_path;
                    if (file_exists($include_path) && !is_dir($include_path)) {
                        require_once $include_path;
                    }
                }
            }

            $controller = parent::getController($request);
            $oModule = $controller[0];
            $act = $controller[1];

            $xml_info = $oModuleModel->getModuleActionXml($module);
            $oModule->ruleset = $xml_info->action->{$act}->ruleset;

            $oModule->setAct($act);

            $module_info->module_type = $xml_info->action->{$act}->type;

            $module_matcher = new \ModuleMatcher();
            $kind = $module_matcher->getKind($act, $module);
            $oModule->module_key = new \ModuleKey($module
                ,$module_info->module_type
                ,$kind);


            $oModule->setModuleInfo($module_info, $xml_info);

            return array($oModule, $oModule->act);
        }
        else
        {
            $module_matcher = new \ModuleMatcher();
            $oModule = $module_matcher->getModuleInstance($act, $module, $oModuleModel, $is_mobile, $is_installed, $module_info);
            return array($oModule, $oModule->act);

        }
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

        require_once _XE_PATH_ . 'classes/module/ModuleHandler.class.php';
        $oModule = \ModuleHandler::getModuleInstance($module, $type, $kind);

        return array($oModule, $method);
    }
}

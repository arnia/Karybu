<?php
namespace GlCMS;
use \Symfony\Component\HttpKernel\Controller;
use Symfony\Component\HttpFoundation\Request;


class ControllerResolver extends Controller\ControllerResolver
{

    public function getController(Request $request)
    {
        $include_paths = $request->attributes->get('_include');
        if (is_array($include_paths)) {
            foreach($include_paths as $include_path) {
                $include_path = _XE_PATH_ . $include_path;
                if (file_exists($include_path) && !is_dir($include_path)) {
                    require_once $include_path;
                }
            }
        }

        return parent::getController($request);
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
        if($count == 2)
        {
            list($module, $type) = $matches[1];
        }
        elseif($count == 3)
        {
            list($module, $kind, $type) = $matches[1];
        }
        else
        {
            throw new \InvalidArgumentException(sprintf('Invalid class name "%s".', $class));
        }

        require_once _XE_PATH_ . 'classes/module/ModuleHandler.class.php';
        $oModule = \ModuleHandler::getModuleInstance($module, $type, $kind);

        return array($oModule, $method);
    }
}

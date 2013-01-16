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
}

<?php
namespace GlCMS;
use \Symfony\Component\HttpKernel\Controller;
use Symfony\Component\HttpFoundation\Request;


class ControllerResolver extends Controller\ControllerResolver
{
    /*public function getController(Request $request)
    {
        if ($request->attributes->has('_controller')) {
            return parent::getController($request);
        }
        else { //cms

        }
    }*/
}
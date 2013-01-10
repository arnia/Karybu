<?php
namespace GlCMS;
use \Symfony\Component\HttpKernel\Controller;
use Symfony\Component\HttpFoundation\Request;


class ControllerResolver extends Controller\ControllerResolver
{
    public function getController(Request $request)
    {

    }

    public function getArguments(Request $request, $controller)
    {

    }
}

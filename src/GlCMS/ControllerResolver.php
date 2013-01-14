<?php

namespace GlCMS;
use \Symfony\Component\HttpKernel\Controller;
use Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\HttpKernel\Log\LoggerInterface;

class ControllerResolver extends Controller\ControllerResolver
{
    public $class;
    public $act;

    public function __construct(LoggerInterface $logger = null)
    {
    }

    public function getController(Request $request)
    {
        if ($controller = parent::getController($request)) {
            return $controller;
        }
    }

    public function getArguments(Request $request, $controller)
    {
        $parentArguments = parent::getArguments($request, $controller);
    }
}

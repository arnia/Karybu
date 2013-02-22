<?php
namespace GlCMS\HttpKernel\Controller;

use GlCMS\Controller\Controller;

class ErrorController extends Controller
{
    function exceptionAction()
    {
        die('Exception controller');
    }
}

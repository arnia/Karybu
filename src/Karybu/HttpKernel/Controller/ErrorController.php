<?php
namespace Karybu\HttpKernel\Controller;

use Karybu\Controller\Controller;

class ErrorController extends Controller
{
    function exceptionAction()
    {
        die('Exception controller');
    }
}

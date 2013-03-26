<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Razvan G Nutu
 * Date: 3/22/13
 * Time: 3:36 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Karybu\EventListener;

use Karybu\Event\ErrorEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Debug\ErrorHandler as SymfonyErrorHandler;


class ErrorHandler extends  SymfonyErrorHandler{

    /** @var SymfonyErrorHandler */
    protected $symHandler;

    private $errors;

    public function __construct($level = null)
    {
        /** @var $handler ErrorHandler */
        $this->symHandler = SymfonyErrorHandler::register($level);
        set_error_handler(array($this, 'handle'));
    }

    public function handle($level, $message, $file, $line, $context)
    {
        try{
            $this->symHandler->handle($level, $message, $file, $line, $context);
        }catch(\ErrorException $e){
            if ($level == E_RECOVERABLE_ERROR || $level == E_ERROR) {
                throw $e;
            }
        }

        $this->errors[] = new ErrorEvent($level, $message, $file, $line, $context);
    }

    public function getErrors() {
        return $this->errors;
    }

}
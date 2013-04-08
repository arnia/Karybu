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
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Debug\ErrorHandler as SymfonyErrorHandler;
use Symfony\Component\HttpKernel\KernelEvents;


class ErrorHandler extends SymfonyErrorHandler implements EventSubscriberInterface
{
    private $injected_logger;
    private static $error_handler_logger;
    private static $errors;

    public function __construct(LoggerInterface $logger = null)
    {
        $this->injected_logger = $logger;
    }

    public function handle($level, $message, $file, $line, $context)
    {
        try {
            parent::handle($level, $message, $file, $line, $context);
        } catch (\ErrorException $e) {
            if ($level == E_RECOVERABLE_ERROR || $level == E_ERROR) {
                throw $e;
            }
        }

        if(!class_exists('Karybu\Event\ErrorEvent')) {
            require_once _XE_PATH_ . 'src/Karybu/Event/ErrorEvent.php';
        }
        self::$errors[] = new ErrorEvent($level, $message, $file, $line, $context);
        if(self::$error_handler_logger)
            self::$error_handler_logger->debug("PHP Error", array($this->getFriendlyErrorType($level), $message, $file, $line));
    }

    public function getErrors()
    {
        return self::$errors;
    }

    private function getFriendlyErrorType($type)
    {
        $return ="";
        if($type & E_ERROR) // 1 //
            $return.='& E_ERROR ';
        if($type & E_WARNING) // 2 //
            $return.='& E_WARNING ';
        if($type & E_PARSE) // 4 //
            $return.='& E_PARSE ';
        if($type & E_NOTICE) // 8 //
            $return.='& E_NOTICE ';
        if($type & E_CORE_ERROR) // 16 //
            $return.='& E_CORE_ERROR ';
        if($type & E_CORE_WARNING) // 32 //
            $return.='& E_CORE_WARNING ';
        if($type & E_COMPILE_ERROR) // 64 //
            $return.='& E_COMPILE_ERROR ';
        if($type & E_COMPILE_WARNING) // 128 //
            $return.='& E_COMPILE_WARNING ';
        if($type & E_USER_ERROR) // 256 //
            $return.='& E_USER_ERROR ';
        if($type & E_USER_WARNING) // 512 //
            $return.='& E_USER_WARNING ';
        if($type & E_USER_NOTICE) // 1024 //
            $return.='& E_USER_NOTICE ';
        if($type & E_STRICT) // 2048 //
            $return.='& E_STRICT ';
        if($type & E_RECOVERABLE_ERROR) // 4096 //
            $return.='& E_RECOVERABLE_ERROR ';
        if($type & E_DEPRECATED) // 8192 //
            $return.='& E_DEPRECATED ';
        if($type & E_USER_DEPRECATED) // 16384 //
            $return.='& E_USER_DEPRECATED ';
        return substr($return,2);
    }

    public static function setLogger(LoggerInterface $logger)
    {
        self::$error_handler_logger = $logger;
    }

    public function injectLogger()
    {
        if (null !== $this->injected_logger) {
            ErrorHandler::setLogger($this->injected_logger);
        }
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * @return array The event names to listen to
     *
     * @api
     */
    public static function getSubscribedEvents()
    {
        return array(KernelEvents::REQUEST => 'injectLogger');
    }
}
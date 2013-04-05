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

        self::$errors[] = new ErrorEvent($level, $message, $file, $line, $context);
        if(self::$error_handler_logger)
            self::$error_handler_logger->debug("PHP Error", array($level, $message, $file, $line));
    }

    public function getErrors()
    {
        return self::$errors;
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
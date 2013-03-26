<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Razvan G Nutu
 * Date: 3/21/13
 * Time: 3:35 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Karybu\EventListener;


use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\FatalErrorException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Debug\ExceptionHandler;

/**
 * Based on ErrorHandler from Symfony
 * @package Karybu\EventListener
 */
class CustomErrorHandler  implements EventSubscriberInterface
{
    const TYPE_DEPRECATION = -100;

    private $levels = array(
        E_WARNING           => 'Warning',
        E_NOTICE            => 'Notice',
        E_USER_ERROR        => 'User Error',
        E_USER_WARNING      => 'User Warning',
        E_USER_NOTICE       => 'User Notice',
        E_STRICT            => 'Runtime Notice',
        E_RECOVERABLE_ERROR => 'Catchable Fatal Error',
        E_DEPRECATED        => 'Deprecated',
        E_USER_DEPRECATED   => 'User Deprecated',
        E_ERROR             => 'Error',
        E_CORE_ERROR        => 'Core Error',
        E_COMPILE_ERROR     => 'Compile Error',
        E_PARSE             => 'Parse',
    );

    private $errors = array();

    private $level;

    private $reservedMemory;

    /**@var LoggerInterface */
    private $logger;

    private $template;

    public function __construct($level = null, LoggerInterface $logger=null){
        set_error_handler(array($this, "handle"));
        register_shutdown_function(array($this, 'handleFatal'));
        $this->logger = $logger;
        $this->setTemplate();
        $this->setLevel($level);
        $this->reservedMemory = str_repeat('x', 10240);
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::RESPONSE =>array(array('includeErrorInResponse', 0))
        );
    }

    public function setLevel($level)
    {
        $this->level = (null === $level ? error_reporting() : $level);
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function includeErrorInResponse(FilterResponseEvent $event){
        $content = $event->getResponse()->getContent();
        $errorsAsHtml = "";
        foreach($this->errors as $error){
            $type = $this->levels[$error->level];
            $errorInfo =
                "Error  : $error->message<br/>".
                "Type   : $type<br/>".
                "File   : $error->file<br/>".
                "Line   : $error->line<br/>";
            $errorsAsHtml.= sprintf($this->template, $errorInfo);
        }
        $content = str_replace("<body>", "<body>".$errorsAsHtml, $content);
        $event->getResponse()->setContent($content);
    }

    public function handle($level, $message, $file, $line, $context)
    {
        if (0 === $this->level) {
            return false;
        }

        $error = new \stdClass();
        $error->level = $level;
        $error->message = $message;
        $error->file = $file;
        $error->line = $line;
        $error->context = $context;

        if ($level & (E_USER_DEPRECATED | E_DEPRECATED)) {
        //if ($level & error_reporting()) {
            if (null !== $this->logger) {
                $stack = version_compare(PHP_VERSION, '5.4', '<') ? array_slice(debug_backtrace(false), 0, 10) : debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);

                $this->logger->warning($message, array('type' => self::TYPE_DEPRECATION, 'stack' => $stack));
            }
            $this->errors[] = $error;
            return true;
        }

        if (error_reporting() & $level && $this->level & $level) {
            if ($level == E_RECOVERABLE_ERROR || $level == E_ERROR){
                $this->errors = array();
                throw new \ErrorException(sprintf('%s: %s in %s line %d', isset($this->levels[$level]) ? $this->levels[$level] : $level, $message, $file, $line), 0, $level, $file, $line);
            }
            $this->errors[] = $error;
        }

        return false;
    }

    public function handleFatal()
    {
        if (null === $error = error_get_last()) {
            return;
        }

        unset($this->reservedMemory);
        $type = $error['type'];
        if (0 === $this->level || !in_array($type, array(E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE))) {
            return;
        }

        // get current exception handler
        $exceptionHandler = set_exception_handler(function() {});
        restore_exception_handler();

        if (is_array($exceptionHandler) && $exceptionHandler[0] instanceof ExceptionHandler) {
            $level = isset($this->levels[$type]) ? $this->levels[$type] : $type;
            $message = sprintf('%s: %s in %s line %d', $level, $error['message'], $error['file'], $error['line']);
            $exception = new FatalErrorException($message, 0, $type, $error['file'], $error['line']);
            $exceptionHandler[0]->handle($exception);
        }
    }

    public function getErrors(){
        return $this->errors;
    }

    // *************** PRIVATE AREA ***************

    private function setTemplate(){
        $this->template =
            "<div class=\"message error\">\n".
                "<p>%s</p>\n".
                "</div>\n";
    }

}
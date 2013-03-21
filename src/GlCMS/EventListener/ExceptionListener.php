<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GlCMS\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Exception\FlattenException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * ExceptionListener.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class ExceptionListener implements EventSubscriberInterface
{
    /** @var \Psr\Log\LoggerInterface */
    private $logger;

    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        /**
         * don't handle the exception until we have the forward feature
         */
        if($this->logger)
            $this->logger->error($event->getException()->getMessage());
        // TODO Forward to mseesageView showing a 500 server error response
        \ModuleHandler::printInvalidRequest();
        exit;

        throw $event->getException();

        static $handling;

        if (true === $handling) {
            return false;
        }

        $handling = true;

        $exception = $event->getException();
        $request = $event->getRequest();

        if (null !== $this->logger) {
            $message = sprintf('%s: %s (uncaught exception) at %s line %s', get_class($exception), $exception->getMessage(), $exception->getFile(), $exception->getLine());
            if (!$exception instanceof HttpExceptionInterface || $exception->getStatusCode() >= 500) {
                $this->logger->critical($message);
            } else {
                $this->logger->error($message);
            }
        } else {
            error_log(sprintf('Uncaught PHP Exception %s: "%s" at %s line %s', get_class($exception), $exception->getMessage(), $exception->getFile(), $exception->getLine()));
        }

        $logger = $this->logger instanceof DebugLoggerInterface ? $this->logger : null;

        $attributes = array(
            'module' => 'message',
            'act' => 'dispMessageException',
            'exception'   => FlattenException::create($exception),
            'logger'      => $logger,
            'format'      => $request->getRequestFormat(),
        );

        //add XE error object if needed
        if ($errorObject = $request->attributes->get('error')) {
            $attributes['error'] = $errorObject;
        }

        $request = $request->duplicate(null, null, $attributes);
        $request->setMethod('GET');

        try {
            $response = $event->getKernel()->handle($request, HttpKernelInterface::SUB_REQUEST, true);
        } catch (\Exception $e) {
            $message = sprintf('Exception thrown when handling an exception (%s: %s)', get_class($e), $e->getMessage());
            if (null !== $this->logger) {
                if (!$exception instanceof HttpExceptionInterface || $exception->getStatusCode() >= 500) {
                    $this->logger->critical($message);
                } else {
                    $this->logger->error($message);
                }
            } else {
                error_log($message);
            }

            // set handling to false otherwise it wont be able to handle further more
            $handling = false;

            // re-throw the exception as this is a catch-all
            return;
        }

        $event->setResponse($response);

        $handling = false;
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::EXCEPTION => array('onKernelException', -128),
        );
    }
}

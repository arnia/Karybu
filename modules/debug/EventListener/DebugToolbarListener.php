<?php
// florin, 3/19/13, 4:29 PM

namespace Karybu\Module\Debug\EventListener;

use Karybu\Event\DBEvents;
use Karybu\Event\ErrorEvent;
use Karybu\Event\QueryEvent;
use Karybu\EventListener\CustomErrorHandler;
use Karybu\Module\Debug\EventListener\DBQueryInfoListener;
use Karybu\Module\Debug\EventListener\QueryErrorListener;
use Karybu\EventListener\ErrorHandler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;


class DebugToolbarListener implements EventSubscriberInterface
{
    const DISABLED = 0;
    const ENABLED = 1;

    protected $mode;
    protected $context;

    private $queryInfoListener;
    private $queryErrorListener;
    private $errorHandler;



    public function __construct(\ContextInstance $context, $mode = self::ENABLED)
    {
        $this->mode = (integer) $mode;
        $this->context = $context;
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::RESPONSE => array('onKernelResponse', -128)
        );
    }

    public function enableQueriesInfo(DBQueryInfoListener $queryInfoListener)
    {
        $this->queryInfoListener = $queryInfoListener;
    }

    public function enableFailedQueriesInfo(QueryErrorListener $queryErrorListener)
    {
        $this->queryErrorListener = $queryErrorListener;
    }

    public function enablePHPErrorsInfo(ErrorHandler $errorHandler)
    {
        $this->errorHandler = $errorHandler;
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        $response = $event->getResponse();
        $request = $event->getRequest();

        // do not capture redirects or modify XML HTTP Requests
        if ($request->isXmlHttpRequest()) {
            return;
        }

        // do not capture modals (or any other request that includes a no_toolbar parameter)
        if ($request->query->has('no_toolbar')) {
            return;
        }

        //TODO treat redirects here

        if (self::DISABLED === $this->mode
            //|| !$response->headers->has('X-Debug-Token')
            || $response->isRedirection()
            || ($response->headers->has('Content-Type') && false === strpos($response->headers->get('Content-Type'), 'html'))
            || 'html' !== $request->getRequestFormat()
        ) {
            return;
        }

        //$this->injectToolbar($response);
    }

    public function isEnabled()
    {
        return self::DISABLED !== $this->mode;
    }

    /**
     * Injects the web debug toolbar into the given Response.
     *
     * @param Response $response A Response instance
     */
    protected function injectToolbar(Response $response)
    {
        if (function_exists('mb_stripos')) {
            $posrFunction   = 'mb_strripos';
            $substrFunction = 'mb_substr';
        } else {
            $posrFunction   = 'strripos';
            $substrFunction = 'substr';
        }
        $content = $response->getContent();
        $pos = $posrFunction($content, '</body>');
        if (false !== $pos) {

            $data = array();

            if($this->queryInfoListener) {
                $queries = $this->queryInfoListener->getQueries();
                $this->context->set('queries', $queries);
                $data['Queries'] = $this->renderView('queries');
            }
            if($this->queryErrorListener) {
                $queries = $this->queryErrorListener->getFailedQueries();
                $this->context->set('failed_queries', $queries);
                $data['Query errors'] = $this->renderView('failed_queries');
            }
            if($this->errorHandler) {
                $errors = $this->errorHandler->getErrors();
                $this->context->set('errors', $errors);
                $data['PHP Errors'] = $this->renderView('php_errors');
            }

            $this->context->set('data', $data);
            $toolbar = $this->renderView('toolbar');
            $content = $substrFunction($content, 0, $pos).$toolbar.$substrFunction($content, $pos);

            $response->setContent($content);
        }
    }

    private function renderView($template_file) {
        $templateHandler = \TemplateHandler::getInstance();
        return $templateHandler->compile('./modules/debug/tpl', $template_file);
    }

}
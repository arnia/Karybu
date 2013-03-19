<?php


class CMSListenerTest extends PHPUnit_Framework_TestCase
{

    /**
     * Test that when variables change in Context they also change in Global context
     * MUST Have for displaying the templates (for now at least)
     */
    public function testContextIsPersistedInGlobals()
    {
        // 1. Make sure method is set on the Request event
        $context = new ContextInstance();
        $listener = new \GlCMS\EventListener\CMSListener($context);
        $events = $listener->getSubscribedEvents();

        $request_events = array_keys($events[\Symfony\Component\HttpKernel\KernelEvents::REQUEST]);
        $this->assertTrue(in_array('putContextInGlobals', $request_events));

        // 2. Check that values are persisted
        // Arrange
        $request = new \Symfony\Component\HttpFoundation\Request();
        $request->attributes->set('oContext', $context);

        $kernel = $this->getMock('\Symfony\Component\HttpKernel\HttpKernelInterface');
        $event = $this->getMock('\Symfony\Component\HttpKernel\Event\GetResponseEvent'
            ,null, array($kernel, $request, \Symfony\Component\HttpKernel\HttpKernelInterface::MASTER_REQUEST));
        global $__Context__;

        // Act
        $listener->putContextInGlobals($event);

        // 3.1. Assert "context" is persisted in globals
        $context->set('name', 'Joe');
        $this->assertTrue(isset($context->getGlobals('__Context__')->name));
        $this->assertEquals('Joe', $context->getGlobals('__Context__')->name);
        $this->assertEquals('Joe', $__Context__->name);

        // 3.2. Assert "lang" is persisted in globals
        global $lang;
        $lang->module_list='Modules List';
        $this->assertEquals('Modules List', $context->getGlobals('lang')->module_list);

        // 3.3. Assert "_cookie" is persisted in globals
        $cookies = &$context->getGlobalCookies();
        $cookies['XDEBUG_SESSION_START'] = '1234';
        $this->assertEquals('1234', $context->context->_COOKIE['XDEBUG_SESSION_START']);

    }
}
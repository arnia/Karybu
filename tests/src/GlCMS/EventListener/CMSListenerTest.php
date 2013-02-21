<?php


class CMSListenerTest extends PHPUnit_Framework_TestCase
{
    public function testContextIsPersistedInGlobals()
    {
        // 1. Make sure method is set on the Request event
        $listener = new \GlCMS\EventListener\CMSListener();
        $events = $listener->getSubscribedEvents();

        $request_events = array_keys($events[\Symfony\Component\HttpKernel\KernelEvents::REQUEST]);
        $this->assertTrue(in_array('doContextGlobalsLink', $request_events));

        // 2. Check that values are persisted
        // Arrange
        $context = new ContextInstance();
        $request = new \Symfony\Component\HttpFoundation\Request();
        $request->attributes->set('oContext', $context);

        $kernel = $this->getMock('\Symfony\Component\HttpKernel\HttpKernelInterface');
        $event = $this->getMock('\Symfony\Component\HttpKernel\Event\GetResponseEvent'
            ,null, array($kernel, $request, \Symfony\Component\HttpKernel\HttpKernelInterface::MASTER_REQUEST));
        global $__Context__;

        // Act
        $listener->doContextGlobalsLink($event);
        $context = $request->attributes->get('oContext');
        $context->set('name', 'Joe');

        // Assert
        $this->assertTrue(isset($context->getGlobals('__Context__')->name));
        $this->assertEquals('Joe', $context->getGlobals('__Context__')->name);
        $this->assertEquals('Joe', $__Context__->name);
    }
}
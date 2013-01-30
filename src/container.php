<?php

use Symfony\Component\DependencyInjection;
use Symfony\Component\DependencyInjection\Reference;

$sc = new DependencyInjection\ContainerBuilder();

$sc->setParameter('debug', true);
$sc->setParameter('charset', 'UTF-8');
$sc->setParameter('routes', include('routes.php'));

$sc->register('context', 'Symfony\Component\Routing\RequestContext');
$sc->register('matcher', 'Symfony\Component\Routing\Matcher\UrlMatcher')->setArguments(array('%routes%', new Reference('context')));
$sc->register('resolver', 'GlCMS\ControllerResolver');
$sc->register('listener.router', 'GlCMS\EventListener\RouterListener')
    ->setArguments(array(new Reference('matcher')));
$sc->register('listener.cms', 'GlCMS\EventListener\CMSListener');
$sc->register('listener.response', 'Symfony\Component\HttpKernel\EventListener\ResponseListener')
    ->setArguments(array('%charset%'));
$sc->register('listener.exception', 'Symfony\Component\HttpKernel\EventListener\ExceptionListener')
    ->setArguments(array('GlCMS\\ErrorController::exceptionAction'));
$sc->register('dispatcher', 'Symfony\Component\EventDispatcher\EventDispatcher')
    ->addMethodCall('addSubscriber', array(new Reference('listener.router')))
    ->addMethodCall('addSubscriber', array(new Reference('listener.cms')))
    ->addMethodCall('addSubscriber', array(new Reference('listener.response')))
    ->addMethodCall('addSubscriber', array(new Reference('listener.exception')));

$sc->register('core', 'GlCMS\Core')->setArguments(array(new Reference('dispatcher'), new Reference('resolver')));

return $sc;

/*
 * loading settings from a (yml?) file:
 * http://symfony.com/doc/current/components/dependency_injection/introduction.html#setting-up-the-container-with-configuration-files
 */
---
layout: docs
title: Karybu, php cms based on Symfony2 components
category: docs
---

# Karybu, php cms based on Symfony2 components

[XpressEngine](http://www.xpressengine.org/) (XE) _is a highly intuitive CMS_ written in php. [Symfony2](http://symfony.com/) _is a PHP framework for web projects_, it's made of components, it's ment to be used in custom projects. We thought of merging these two. Karybu is XpressEngine on Symfony components.

_One great benefit of using the Symfony2 components is the interoperability between all applications using them_. This, in particular, refers to [HttpKernel](http://symfony.com/doc/master/components/http_kernel/introduction.html#the-workflow-of-a-request)'s HttpKernelInterface. _Frameworks and applications that implement this interface are fully interoperable_.

This is how our front controller (index.php) looks like:

```php
use Symfony\Compone[composer.json](https://github.com/arnia/Karybu/blob/master/composer.json)nt\HttpFoundation\Request;
use Karybu\HttpKernel\Kernel;

$isCommandLine = ( php_sapi_name() == 'cli' );

require dirname(__FILE__) . '/config/config.inc.php';

$validCommandLineCall = $isCommandLine && isset($argv[1]) && filter_var($argv[1], FILTER_VALIDATE_URL);

//create request using first call parameter if the script is called from the console with a valid url as first param
$request = $validCommandLineCall ? Request::create($argv[1]) : Request::createFromGlobals();

$kernel = new Kernel('dev', true);

$response = $kernel->handle($request);
$response->send();

$kernel->terminate($request, $response);
```
We're using Symfony\Component\HttpKernel as our implementation of HttpKernelInterface, the $kernel you see above doing the heavy handle() work, so this next diagram extracted from the components's docs describes our flow:

![HttpKernel request-response flow](http://symfony.com/doc/master/_images/request-response-flow.png)

In order to convert [HttpFoundation](http://symfony.com/doc/master/components/http_foundation/index.html)'s $request to $response we're using EventDispatcher to inject our cms code into kernel [events](http://symfony.com/doc/master/components/http_kernel/introduction.html#httpkernel-driven-by-events), so if you've already parsed the EventDispatcher's [docs](http://symfony.com/doc/master/components/event_dispatcher/index.html), you should feel comfortable observing how we injected our old code into HttpKernel.

./src/Karybu/EventListener/[CMSListener.php](https://github.com/arnia/Karybu/blob/master/src/Karybu/EventListener/CMSListener.php) :

```php
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array(
                array('setupLegacyDependencies', 50),
                array('initializeContextRequestArguments', 48),
                array('initializeDatabaseSettings', 46),
                //32 is router listener
                array('doContextInit', 30),
                array('doContextCheckSSO', 28),
                array('checkModuleHandlerInit', 26),
                array('prepareRequestForResolving', 24),
                array('checkForErrorsAndPrepareMobileStatus', 22)
            ),
            KernelEvents::CONTROLLER => array(
                array('checkUserPermissions', 100),
                array('injectCustomHeaderAndFooter', 96),
                array('executeTriggersAddonsAndOthersBefore', 90)
            ),
            KernelEvents::EXCEPTION => array('onKernelException', -128),
            KernelEvents::TERMINATE => 'onTerminate',
            KernelEvents::VIEW => array(
                array('executeTriggersAddonsAndOthersAfter', 100),
                array('makeResponse', 99),
            )
        );
    }
```
Again, you should check out the docs if you're not familiar with stuff like [KernelEvents::REQUEST](http://symfony.com/doc/master/components/http_kernel/introduction.html#the-kernel-request-event).

Next we could do (or already did) the following:

* pass input variables from [routes](https://github.com/arnia/Karybu/blob/master/config/routes.yml) (we use [Routing](http://symfony.com/doc/master/components/routing)), to controllers - but we had to put that (an utopical developer friendly module(/bundle?) structure) on hold for the moment, as much more work was still needed at splitting cms code into proper events and detecting and fixing all kinds of (sometimes strange) problems caused mostly by the old code being old.
* reverse proxy in front of the front controller
* [Twig](http://twig.sensiolabs.org/) or possibly any templating language if this feature's done right instead of our current hard to maintain and too custom templating system
* lose backward compatibility as soon as we have a new module structure
* routes, actual friendly urls, which were flawed in the old cms

Here are the components/libs we're using, as an extract from [composer.json](https://github.com/arnia/Karybu/blob/master/composer.json):

```php
{
    "require": {
        "symfony/class-loader": "2.2.*",
        "symfony/http-foundation": "2.2.*",
        "symfony/routing": "2.2.*",
        "symfony/http-kernel": "2.2.*",
        "symfony/event-dispatcher": "2.2.*",
        "symfony/dependency-injection": "2.2.*",
        "symfony/config": "2.2.*",
        "symfony/yaml": "2.2.*",
        "symfony/stopwatch":"2.2.*",
        "monolog/monolog": "1.4.*",
        "symfony/console": "2.2.*"
    },
```

* **ClassLoader** - we have [PSR-0](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md) in ./src and inside each module.
* **HttpFoundation** - $request, $response(, $session?)
* **Routing** - routes are stored in the main [config/routes.yml](https://github.com/arnia/Karybu/blob/master/config/routes.yml) and each module can inject its own routes into the main collection by its own conf/routes.yml
* **EventDispatcher** - beside its use with httpkernel, we're planning on filling our cms code with events.
* DependencyInjection - we have a main [service container](https://github.com/arnia/Karybu/blob/master/config/config.yml) which can be [extended](https://github.com/arnia/Karybu/blob/master/modules/debug/conf/services.yml) by modules in the way Symfony2 bundles do.
* **Config** - while the old cms was keeping its configs in xmls, we're now using yaml. Also, Config knows how to load routes or services.
* Stopwatch, Monolog - for logging and the debug toolbar

As we're using the front controller pattern with HttpKernel, we can now run and debug the application in the console by running `php index.php <cms_url_here>`

See the [Debug](https://github.com/arnia/Karybu/tree/master/modules/debug) module for an example of the new kind of modules.
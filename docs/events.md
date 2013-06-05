---
layout: docs
title: Events
category: docs
---

Karybu events
====

In order to understand not only what Karybu's events are,
but how you can hook to them and what information is available
at each step of the application execution, it's best to start
with an overview of a request's lifecycle in Karybu - what happens
behind the sceenes from the moment a Request is received,
until the moment a Response is sent back to the client?

The Karybu core is built on top of Symfony2's HttpKernel component,
so a it helps a lot to also understand what events it exposes and
what happens step by step, You can find a great help article on
this on the [HttpKernel Component documentation page](http://symfony.com/doc/2.0/components/http_kernel/introduction.html).
I highly recommend looking over it before going further.

Karybu request lifecycle overview
----------------------------------

1. *KernelEvents::REQUEST*
     - Application initialization (Context init)
          - loads request arguments, database settings, application settings
          - initializes languages
          - starts session, loads authentication ingo
          - validate variablels against XSS, check SSL
     - **KarybuEvents::BEFORE_MODULE_INIT**
     - Module initialization (ModuleHandler init)
          - retrieves information about the current module being requested (document_srl, module, module_srl, act etc.) and makes sure the request arguments are valid
          - loads module information from the database (a record of the k_modules table - module_srl, browser_title, layout_srl etc.)
          - returns an error object (messageView if an error is found)
2. *Resolve controller* (ControllerResolver->getController) - returns the current module instance
- *KernelEvents::CONTROLLER*
      - Check for user permissions, check rulesets, inject custom header and footer
      - **KarybuEvents::BEFORE_MODULE_PROC**
- *Resolve arguments* (ControllerResolver->getArguments)
- *Call controller*
- *KernelEvents::VIEW*
      - **KarybuEvents::AFTER_MODULE_PROC**
      - redirect if Validator found an error ("flash" functionality)
      - load current layout and required menus
      - makeResponse
          - parses the current template file, based on the request type (HtmlDisplayHandler, JSON request handler)
          - **KarybuEvents::BEFORE_DISPLAY_CONTENT**
          - prepare to print (update assets' paths, move script tags to header, sets favicons, load common js and css files, load common layout
          - GZIP content, if enabled
          - return Response object containing compiled content
- *KernelEvents::RESPONSE*
- *Response->send*
- *KernelEvents::TERMINATE*
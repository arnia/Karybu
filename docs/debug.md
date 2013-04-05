Karybu debugging tools
====

It is always helpful as a developer to understand what happens with each request in an app. This is why Karybu gathers a series of information to make it clearer what queries were executed in a request, what exceptions were thrown and others.

Configuring Karybu
-----

There are mainly two configuration settings that influence the app's behaviour:

* __kernel.environment__ - can be `prod` or `dev`

  Depending on this settings, a different configuration file is loaded: *config_dev.yml* or *config_prod.yml* (from the *./config*) folder. 
  
* __kernel.debug__ - can be `true` or `false` 

  Depending on this settings, certain features are enabled/disabled, like detailed debug information or gzip compression.

These are set in the `index.php`, right at the beggining of each request, when the kernel is created:
```
$kernel = new Kernel('prod', false)
```
The first parameter is the __kernel.environment__ and the second is the __kernel.debug__ settings.

Info gathered when debug is off
----

Regardless of the environment, Karybu gathers information regarding errors that appeared in the app: database query errors, PHP errors or exceptions thrown.

* __Database query errors__

  Available in the *db_errors_dev.log* file.
  
* __PHP errors__

  Available in the *deprecated_errors_dev.log* file.
  
* __Exceptions__

  Available in *errors_dev.log* file

Info gathered when debug in on
-----

Besides all the information that is gathered when debug is on, there are a few other infos the developer has available when debug is on:

* __Request / response summary information__

  Provides information regarding:
  - The http method and request URI
  - The total duration of the app kernel execution

  *Log file name: dev.log*

* __Database queries info__

  Provides a list of all database queries that were executed during a request and info about them:
  - query duration
  - query sql text
  - originating xml query file name

  It also provies the total duration of all queries executed and their count.

  *Log file name: db_info_dev.log*

* __Slow queries information__

  Provides a list of all queries that took longer than a given duration to execute.

  The threshold can be adjusted with the `slow_queries_threshold` setting of the debug module (see below section -  "Debug module configuration settings"

  *Log file name: db_slow_query_dev.log*


Global configuration settings
----
* __gzip compression__

Depends on `kernel.debug` - when debugging is on, gzip is off and vice-versa. This settings can be manually overriden in *config_&lt;env&gt;.yml*:
```
         parameters: 
            cms.gz_encoding: false 
```


Debug module configuration settings
----

* __level__ defines the error level that should be logged; defaults to __error__ in production and __debug__ in development; available values:  DEBUG, INFO, WARNING, ERROR, CRITICAL, ALERT.
* __toolbar__ whether to load the developer toolbar or not; this appears on every page of the app showing detailed info about the current request
*  __slow_queries_threshold__ available just when debug is enabled
* __handlers__ array of all handlers to use for outputting the logs; available handlers are:
  * file - writes logs on the disk
  * chrome - writes logs in the chrome developer toolbar (TODO)
  * firebug - writes logs in the firebug (firefox extension) (TODO)




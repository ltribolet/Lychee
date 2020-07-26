<?php

declare(strict_types=1);

return [
    /*
     |--------------------------------------------------------------------------
     | Debugbar Settings
     |--------------------------------------------------------------------------
     |
     | Debugbar is enabled by default, when debug is set to true in app.php.
     | You can override the value by setting enable to true or false instead of null.
     |
     | You can provide an array of URI's that must be ignored (eg. 'api/*')
     |
     */

    'enabled' => env('DEBUGBAR_ENABLED', false),
    'except' => ['telescope*'],

    /*
     |--------------------------------------------------------------------------
     | Storage settings
     |--------------------------------------------------------------------------
     |
     | DebugBar stores data for session/ajax requests.
     | You can disable this, so the debugbar stores data in headers/session,
     | but this can cause problems with large data collectors.
     | By default, file storage (in the storage folder) is used. Redis and PDO
     | can also be used. For PDO, run the package migrations first.
     |
     */
    'storage' => [
        'enabled' => true,
        // redis, file, pdo, custom
        'driver' => 'file',
        // For file driver
        'path' => storage_path('debugbar'),
        // Leave null for default connection (Redis/PDO)
        'connection' => null,
        // Instance of StorageInterface for custom driver
        'provider' => '',
    ],

    /*
     |--------------------------------------------------------------------------
     | Vendors
     |--------------------------------------------------------------------------
     |
     | Vendor files are included by default, but can be set to false.
     | This can also be set to 'js' or 'css', to only include javascript or css vendor files.
     | Vendor files are for css: font-awesome (including fonts) and highlight.js (css files)
     | and for js: jquery and and highlight.js
     | So if you want syntax highlighting, set it to true.
     | jQuery is set to not conflict with existing jQuery scripts.
     |
     */

    'include_vendors' => true,

    /*
     |--------------------------------------------------------------------------
     | Capture Ajax Requests
     |--------------------------------------------------------------------------
     |
     | The Debugbar can capture Ajax requests and display them. If you don't want this (ie. because of errors),
     | you can use this option to disable sending the data through the headers.
     |
     | Optionally, you can also send ServerTiming headers on ajax requests for the Chrome DevTools.
     */

    'capture_ajax' => true,
    'add_ajax_timing' => false,

    /*
     |--------------------------------------------------------------------------
     | Custom Error Handler for Deprecated warnings
     |--------------------------------------------------------------------------
     |
     | When enabled, the Debugbar shows deprecated warnings for Symfony components
     | in the Messages tab.
     |
     */
    'error_handler' => false,

    /*
     |--------------------------------------------------------------------------
     | Clockwork integration
     |--------------------------------------------------------------------------
     |
     | The Debugbar can emulate the Clockwork headers, so you can use the Chrome
     | Extension, without the server-side code. It uses Debugbar collectors instead.
     |
     */
    'clockwork' => false,

    /*
     |--------------------------------------------------------------------------
     | DataCollectors
     |--------------------------------------------------------------------------
     |
     | Enable/disable DataCollectors
     |
     */

    'collectors' => [
        // Php version
        'phpinfo' => true,
        // Messages
        'messages' => true,
        // Time Datalogger
        'time' => true,
        // Memory usage
        'memory' => true,
        // Exception displayer
        'exceptions' => true,
        // Logs from Monolog (merged in messages if enabled)
        'log' => true,
        // Show database (PDO) queries and bindings
        'db' => true,
        // Views with their data
        'views' => true,
        // Current route information
        'route' => true,
        // Display Laravel authentication status
        'auth' => false,
        // Display Laravel Gate checks
        'gate' => true,
        // Display session data
        'session' => true,
        // Only one can be enabled..
        'symfony_request' => true,
        // Catch mail messages
        'mail' => false,
        // Laravel version and environment
        'laravel' => false,
        // All events fired
        'events' => false,
        // Regular or special Symfony request logger
        'default_request' => false,
        // Add the latest log messages
        'logs' => false,
        // Show the included files
        'files' => false,
        // Display config settings
        'config' => false,
        // Display cache events
        'cache' => false,
        // Display models
        'models' => true,
    ],

    /*
     |--------------------------------------------------------------------------
     | Extra options
     |--------------------------------------------------------------------------
     |
     | Configure some DataCollectors
     |
     */

    'options' => [
        'auth' => [
            // Also show the users name/email in the debugbar
            'show_name' => true,
        ],
        'db' => [
            // Render SQL with the parameters substituted
            'with_params' => true,
            // Use a backtrace to find the origin of the query in your files.
            'backtrace' => true,
            // Add the queries to the timeline
            'timeline' => false,
            // Show EXPLAIN output on queries
            'explain' => [
                'enabled' => false,
                'types' => [
                    'SELECT',
                    // // workaround ['SELECT'] only. https://github.com/barryvdh/laravel-debugbar/issues/888 ['SELECT', 'INSERT', 'UPDATE', 'DELETE']; for MySQL 5.6.3+
                ],
            ],
            // Show hints for common mistakes
            'hints' => true,
        ],
        'mail' => [
            'full_log' => false,
        ],
        'views' => [
            //Note: Can slow down the application, because the data can be quite large..
            'data' => false,
        ],
        'route' => [
            // show complete route on bar
            'label' => true,
        ],
        'logs' => [
            'file' => null,
        ],
        'cache' => [
            // collect cache values
            'values' => true,
        ],
    ],

    /*
     |--------------------------------------------------------------------------
     | Inject Debugbar in Response
     |--------------------------------------------------------------------------
     |
     | Usually, the debugbar is added just before </body>, by listening to the
     | Response after the App is done. If you disable this, you have to add them
     | in your template yourself. See http://phpdebugbar.com/docs/rendering.html
     |
     */

    'inject' => true,

    /*
     |--------------------------------------------------------------------------
     | DebugBar route prefix
     |--------------------------------------------------------------------------
     |
     | Sometimes you want to set route prefix to be used by DebugBar to load
     | its resources from. Usually the need comes from misconfigured web server or
     | from trying to overcome bugs like this: http://trac.nginx.org/nginx/ticket/97
     |
     */
    'route_prefix' => '_debugbar',

    /*
     |--------------------------------------------------------------------------
     | DebugBar route domain
     |--------------------------------------------------------------------------
     |
     | By default DebugBar route served from the same domain that request served.
     | To override default domain, specify it as a non-empty value.
     */
    'route_domain' => null,
];

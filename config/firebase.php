<?php

declare(strict_types=1);

return [
    /*
     * ------------------------------------------------------------------------
     * Default Firebase project
     * ------------------------------------------------------------------------
     */

    'default' => env('FIREBASE_PROJECT', 'app'),

    /*
     * ------------------------------------------------------------------------
     * Firebase project configurations
     * ------------------------------------------------------------------------
     */

    'projects' => [
        'proper-9d82d' => [

            /*
             * ------------------------------------------------------------------------
             * Credentials / Service Account
             * ------------------------------------------------------------------------
             *
             * In order to access a Firebase project and its related services using a
             * server SDK, requests must be authenticated. For server-to-server
             * communication this is done with a Service Account.
             *
             * If you don't already have generated a Service Account, you can do so by
             * following the instructions from the official documentation pages at
             *
             * https://firebase.google.com/docs/admin/setup#initialize_the_sdk
             *
             * Once you have downloaded the Service Account JSON file, you can use it
             * to configure the package.
             *
             * If you don't provide credentials, the Firebase Admin SDK will try to
             * auto-discover them
             *
             * - by checking the environment variable FIREBASE_CREDENTIALS
             * - by checking the environment variable GOOGLE_APPLICATION_CREDENTIALS
             * - by trying to find Google's well known file
             * - by checking if the application is running on GCE/GCP
             *
             * If no credentials file can be found, an exception will be thrown the
             * first time you try to access a component of the Firebase Admin SDK.
             *
             */

            // 'credentials' => env('FIREBASE_CREDENTIALS', env('GOOGLE_APPLICATION_CREDENTIALS')),
            'credentials' => [
                "type"=> "service_account",
                "project_id"=> "proper-9d82d",
                "private_key_id"=> "c1946efbe76e66715e81bb7e3e4846d537b1e540",
                "private_key"=> "-----BEGIN PRIVATE KEY-----\nMIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQDQcz57L9w0gRB9\nhpLiiA28zHkQdP2m0piusnahwVEcfVek1DuPxxckzi6aA3FwjqaQnjgpEdv7aI2U\nxHLhIdA+ElNImjIrasbGFxswRg1o4BsRTRV17QIIvDD66GSDzmTz+5S8Os07UoGl\nYvSds9cV3zVk7xpagKxfyoF8xUnuJvpHcb8qF+Br0k0D8K2SVYEAh21Pkff39A6j\niGUUBY3azy9yyMGE6XagMvQjSLCtQ9P0j5QmUrTaBGVPLN2wCvcMNLgUbBAxwiVn\nCWl3P8oMWtcRx7x+EazGzd7nmdt6u43cd4gEGTkN8lvwM3GLHjaARddsPFLz2iiJ\nzr0bGwgPAgMBAAECggEAD0c7H6VQ4wpIrqKj9nWZaCqmfvXMN6N87GUJO+7i5o8r\nUtn5aZ7ii+CO2twAvwq9m6D1JdF4ybMOmYZSkzy6uin2r74ZikQfwJol9IJV7kdq\nxHhdZ9DQ0toUTvekkmKY1KcubiNzMnNoLDqWCk8Jp0E+dSDvUg3XVIgPuJjvQ8S2\nsFMGuFk0XC/G+bQTWB7Jd3DQATtNfF8PFBFe1SgaTiiwb4I65WezVnXxgqzu5IXz\nxh4U/0CX+AhjyX9B6cfsJSPYMy3vpJsI/8MYVi1EN3NnhIVWq7SMrvEI75QMIQrQ\nqozqkGaLs4WJOPbfSdAbwvI2gw4V7U0xYz4Z/+eMGQKBgQDzARZXVVF19r4ZEqBY\nIPrVMxcWYXVpzMu7FObAR8lqR6bbSz8svewbxm8ZCnHMe9wFtabk5aYeQ5fufFzz\no2lWpZ0ztIO9kN58w0QL51WjyUGpOiXiy53BfV394lldOnci1yhHHiwQ1UJ9QfUE\ned1jWvYQmmLKj16Sc5GOg6H9uQKBgQDbmRXTySkvoERK4w4Ia/24jexwS8Kb/T3w\noseKmiQ9CSAJUYZPAfRxF62iMudGmoKtR4YOmvM+ssHZ+9+IyO3yvMUDMAPirmPT\njXZTE0ev21qt/iv6vMDorQhjPVGQIGZvel3SzTEg+ESaCY8S4h6ouG6wjhSjJwBO\nWx9ndOHYBwKBgQCiQAGYwkCn0N3Qg91Huo2AOKKbsOrif3kwKp5/l+7l5X2FQlKm\nHxE84ltfjte4vqKDtWv/vU4TOvKAq8ysaFl3HxE9arPjqIzFJOOURxupRkFvKoIN\nUgK9JGXTlIQyeUz+mEYuZfjqLFo+pAFiwbOOGTfekhaQRPXSNxPi4gUREQKBgEjG\nX/Ry6wJMk7VZr/HckBYUerHweYsmjttrpsNpN+8+Ue6kpOUUGcVM+o8RXZIJbJsu\nY9/9O/WgWhv6m+cB59GU+5mF1RPPhWe7ruXzMsO150RYQozy9t9lUK1KyfgAtNHm\n9KcCt3Bctqdx2YeBhnWVwaEjRPWY3EIbcrnfg2ULAoGAFBbA9YLZZO6zjIeVBGkn\nMgTBUGIubvs9rlYsu9P6+Wr2+rvEwqmK5Z8hrNlvhrnp2PzkTL+6ITBuPOAti/W4\nAahk2+UhTQgCYKJwcze6oTfUnUG47rkLgaJRtL1ee58kZJLbT3ri6v0yZCX7oQ4L\nPGcqkZ7RJTft//aSi9wlGfM=\n-----END PRIVATE KEY-----\n",
                "client_email"=> "proper-9d82d@appspot.gserviceaccount.com",
                "client_id"=> "107778552644996813088",
                "auth_uri"=> "https://accounts.google.com/o/oauth2/auth",
                "token_uri"=> "https://oauth2.googleapis.com/token",
                "auth_provider_x509_cert_url"=> "https://www.googleapis.com/oauth2/v1/certs",
                "client_x509_cert_url"=> "https://www.googleapis.com/robot/v1/metadata/x509/proper-9d82d%40appspot.gserviceaccount.com",
                "universe_domain"=> "googleapis.com"
            ],

            /*
             * ------------------------------------------------------------------------
             * Firebase Auth Component
             * ------------------------------------------------------------------------
             */

            'auth' => [
                'tenant_id' => env('FIREBASE_AUTH_TENANT_ID'),
            ],

            /*
             * ------------------------------------------------------------------------
             * Firebase Realtime Database
             * ------------------------------------------------------------------------
             */

            'database' => [

                /*
                 * In most of the cases the project ID defined in the credentials file
                 * determines the URL of your project's Realtime Database. If the
                 * connection to the Realtime Database fails, you can override
                 * its URL with the value you see at
                 *
                 * https://console.firebase.google.com/u/1/project/_/database
                 *
                 * Please make sure that you use a full URL like, for example,
                 * https://my-project-id.firebaseio.com
                 */

                'url' => env('FIREBASE_DATABASE_URL'),

                /*
                 * As a best practice, a service should have access to only the resources it needs.
                 * To get more fine-grained control over the resources a Firebase app instance can access,
                 * use a unique identifier in your Security Rules to represent your service.
                 *
                 * https://firebase.google.com/docs/database/admin/start#authenticate-with-limited-privileges
                 */

                // 'auth_variable_override' => [
                //     'uid' => 'my-service-worker'
                // ],

            ],

            'dynamic_links' => [

                /*
                 * Dynamic links can be built with any URL prefix registered on
                 *
                 * https://console.firebase.google.com/u/1/project/_/durablelinks/links/
                 *
                 * You can define one of those domains as the default for new Dynamic
                 * Links created within your project.
                 *
                 * The value must be a valid domain, for example,
                 * https://example.page.link
                 */

                'default_domain' => env('FIREBASE_DYNAMIC_LINKS_DEFAULT_DOMAIN'),
            ],

            /*
             * ------------------------------------------------------------------------
             * Firebase Cloud Storage
             * ------------------------------------------------------------------------
             */

            'storage' => [

                /*
                 * Your project's default storage bucket usually uses the project ID
                 * as its name. If you have multiple storage buckets and want to
                 * use another one as the default for your application, you can
                 * override it here.
                 */

                'default_bucket' => env('FIREBASE_STORAGE_DEFAULT_BUCKET'),

            ],

            /*
             * ------------------------------------------------------------------------
             * Caching
             * ------------------------------------------------------------------------
             *
             * The Firebase Admin SDK can cache some data returned from the Firebase
             * API, for example Google's public keys used to verify ID tokens.
             *
             */

            'cache_store' => env('FIREBASE_CACHE_STORE', 'file'),

            /*
             * ------------------------------------------------------------------------
             * Logging
             * ------------------------------------------------------------------------
             *
             * Enable logging of HTTP interaction for insights and/or debugging.
             *
             * Log channels are defined in config/logging.php
             *
             * Successful HTTP messages are logged with the log level 'info'.
             * Failed HTTP messages are logged with the log level 'notice'.
             *
             * Note: Using the same channel for simple and debug logs will result in
             * two entries per request and response.
             */

            'logging' => [
                'http_log_channel' => env('FIREBASE_HTTP_LOG_CHANNEL'),
                'http_debug_log_channel' => env('FIREBASE_HTTP_DEBUG_LOG_CHANNEL'),
            ],

            /*
             * ------------------------------------------------------------------------
             * HTTP Client Options
             * ------------------------------------------------------------------------
             *
             * Behavior of the HTTP Client performing the API requests
             */

            'http_client_options' => [

                /*
                 * Use a proxy that all API requests should be passed through.
                 * (default: none)
                 */

                'proxy' => env('FIREBASE_HTTP_CLIENT_PROXY'),

                /*
                 * Set the maximum amount of seconds (float) that can pass before
                 * a request is considered timed out
                 *
                 * The default time out can be reviewed at
                 * https://github.com/kreait/firebase-php/blob/6.x/src/Firebase/Http/HttpClientOptions.php
                 */

                'timeout' => env('FIREBASE_HTTP_CLIENT_TIMEOUT'),

                'guzzle_middlewares' => [],
            ],
        ],
    ],
];
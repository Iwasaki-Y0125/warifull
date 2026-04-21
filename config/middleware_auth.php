<?php

return [
    'enabled' => env('MIDDLEWARE_AUTH_ENABLED', false),
    'username' => env('MIDDLEWARE_AUTH_USER', ''),
    'password' => env('MIDDLEWARE_AUTH_PASSWORD', ''),
    'realm' => env('MIDDLEWARE_AUTH_REALM', 'Warifull Demo'),
];

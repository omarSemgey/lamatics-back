<?php

return [

    'paths' => ['api/*', 'api/auth/*', 'counts/*', 'client-logs'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['https://lamatics.onrender.com'], 

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true, 
];

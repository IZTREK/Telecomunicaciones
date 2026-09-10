<?php

// Equivalente a la sección "Cors" de appsettings.json: solo el origen donde
// corre el frontend (Live Server) puede llamar a la API.
return [
    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => array_map(
        'trim',
        explode(',', env('FRONTEND_ORIGINS', 'http://127.0.0.1:5500,http://localhost:5500'))
    ),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,
];

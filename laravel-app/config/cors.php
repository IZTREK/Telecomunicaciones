<?php

// Equivalente a la sección "Cors" de appsettings.json: permite el origen donde
// corre el frontend (Live Server) y cualquier subdominio generado por ngrok.
return [
    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => array_map(
        'trim',
        explode(',', env('FRONTEND_ORIGINS') ?: 'http://127.0.0.1:5500,http://localhost:5500,http://127.0.0.1:5501,http://localhost:5501')
    ),

    'allowed_origins_patterns' => [
        '#^https://.*\.ngrok-free\.app$#',
        '#^https://.*\.ngrok-free\.dev$#',
        '#^https://.*\.ngrok\.io$#',
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,
];
<?php

// El frontend y la API se sirven desde el MISMO origen (nginx en :8080, o el
// túnel de ngrok que apunta a él), así que el navegador no necesita CORS.
// FRONTEND_ORIGINS solo hace falta si algún día el frontend se sirve desde
// otro origen; vacío = ningún origen externo permitido.
return [
    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('FRONTEND_ORIGINS', ''))
    ))),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,
];

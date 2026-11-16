<?php

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    // Domínios do frontend autorizados a chamar esta API.
    // Lista separada por vírgula em CORS_ALLOWED_ORIGINS (sem barra no final).
    'allowed_origins' => array_filter(array_map(
        'trim',
        explode(',', env('CORS_ALLOWED_ORIGINS', ''))
    )),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // false: autenticação é via header "Authorization: Bearer ..." (Sanctum token), não cookie de sessão.
    'supports_credentials' => false,

];

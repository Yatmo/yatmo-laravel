<?php

return [
    // Backend key, server to server: never sent to the browser. https://documentation.yatmo.com/license
    'key' => env('YATMO_KEY'),

    // Frontend key, the one locked to your domains, embedded in the map iframe.
    'frontend_key' => env('YATMO_FRONTEND_KEY'),

    // Country of your properties: BE, FR, NL, LU, CH, DE, IT, ES, PT, IE, UK, AT, CA, GR, MA, AU, HR, MT, SI, RS, CY, BA, ME, BG, AL
    'country' => env('YATMO_COUNTRY', 'BE'),

    // Language of labels and texts (EN, FR, NL, DE...). Null follows the application locale.
    'language' => env('YATMO_LANGUAGE'),

    // Cache of the neighbourhood text and places, in days (a neighbourhood rarely changes). 0 disables it.
    'cache_days' => (int) env('YATMO_CACHE_DAYS', 30),

    // Cache store, null for the default one.
    'cache_store' => env('YATMO_CACHE_STORE'),

    // Request timeout in seconds.
    'timeout' => (int) env('YATMO_TIMEOUT', 15),

    // Defaults of the <x-yatmo-map> component (any iframe parameter: mode, zoom, marker, isochrone...).
    'map' => [
        'mode' => 'overlay',
        'zoom' => 15,
        'height' => '560px',
    ],
];

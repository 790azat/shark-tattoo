<?php

return [
    // Значения по умолчанию; всё редактируется в админке (таблица settings)
    'name' => 'Shark Tattoo',
    'handle' => '_shark__tattoo',
    'instagram' => 'https://www.instagram.com/_shark__tattoo/',
    'locales' => ['hy', 'ru', 'en'],
    'default_locale' => 'hy',
    'admin_password' => env('ADMIN_PASSWORD'),
    'setup_token' => env('SETUP_TOKEN'),
];

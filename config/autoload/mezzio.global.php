<?php

declare(strict_types=1);

use Laminas\ConfigAggregator\ConfigAggregator;

// echo base64_encode(openssl_random_pseudo_bytes(32));
// die;

return [
    // Toggle the configuration cache. Set this to boolean false, or remove the
    // directive, to disable configuration caching. Toggling development mode
    // will also disable it by default; clear the configuration cache using
    // `composer clear-config-cache`.
    ConfigAggregator::ENABLE_CACHE => false,
    // Enable debugging; typically used to provide debugging information within templates.
    'debug' => false,
    // authentication configuration
    'authentication' => [
        'tablename' => 'users',
        'username' => 'email',
        'password' => 'password',
        'form' => [
            'username' => 'username',
            'password' => 'password',
        ]
    ],
    'translator' => [
        'locale' => [
            'tr', // default locale
            'en'  // fallback locale
        ],
        'translation_file_patterns' => [
            [
                'type' => 'PhpArray',
                'base_dir' => PROJECT_ROOT . '/data/language',
                'pattern' => '%s/messages.php'
            ],
            [
                'type' => 'PhpArray',
                'base_dir' => PROJECT_ROOT . '/data/language',
                'pattern' => '%s/labels.php',
                'text_domain' => 'labels',
            ],
            [
                'type' => 'PhpArray',
                'base_dir' => PROJECT_ROOT . '/data/language',
                'pattern' => '%s/templates.php',
                'text_domain' => 'templates',
            ],
        ],
    ],

    // 'mezzio' => [
    //     // Provide templates for the error handling middleware to use when
    //     // generating responses.
    //     'error_handler' => [
    //         'template_404'   => 'error::404',
    //         'template_error' => 'error::error',
    //     ],
    // ],
];

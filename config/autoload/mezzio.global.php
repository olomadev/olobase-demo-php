<?php

declare(strict_types=1);

// echo base64_encode(openssl_random_pseudo_bytes(32));
// die;

return [
    //
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

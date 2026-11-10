<?php
$EM_CONF[$_EXTKEY] = [
    'title' => 'CMP Media Renderer',
    'description' => 'Overrides the default YouTube & Vimeo output with markup supporting Cookiebot CMP',
    'category' => 'fe',
    'author' => 'Christian Baur',
    'author_email' => 'c.baur@i-san.de',
    'author_company' => 'Christian Baur',
    'state' => 'stable',
    'version' => '1.1.0',
    'constraints' => [
        'depends' => [
            'typo3' => '13.4.0-14.4.99',
            'php' => '8.2.0-8.5.99',
        ],
        'conflicts' => [
        ],
        'suggests' => [
        ],
    ],
    'autoload' => [
        'psr-4' => [
            'Cbaur\\CmpMediarenderer\\' => 'Classes'
        ]
    ],
];
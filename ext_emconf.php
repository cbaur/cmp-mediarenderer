<?php
$EM_CONF[$_EXTKEY] = [
    'title' => 'CMP Media Renderer',
    'description' => 'Overrides the default YouTube & Vimeo output with markup supporting Cookiebot CMP',
    'category' => 'fe',
    'author' => 'Christian Baur',
    'author_email' => 'c.baur@i-san.de',
    'author_company' => 'Christian Baur',
    'state' => 'stable',
    'version' => '1.0.0',
    'clearCacheOnLoad' => true,
    'constraints' => [
        'depends' => [
            'typo3' => '10.4.0-12.4.99',
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
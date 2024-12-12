<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'DeepL Translate - Contribution Helper',
    'description' => 'Collection of contribution helper for extension ddev environment',
    'category' => 'backend',
    'author' => 'web-vision GmbH Team',
    'author_company' => 'web-vision GmbH',
    'author_email' => 'hello@web-vision.de',
    'state' => 'stable',
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'php' => '8.1.0-8.4.99',
            'typo3' => '12.4.0-12.4.99',
            'backend' => '12.4.0-12.4.99',
            'frontend' => '12.4.0-12.4.99',
            'install' => '12.4.0-12.4.99',
            'setup' => '12.4.0-12.4.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
    'autoload' => [
        'psr-4' => [
            'WebVision\\Deepltranslate\\Core\\' => 'Classes',
        ],
    ],
];

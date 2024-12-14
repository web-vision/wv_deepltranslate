<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'TF BackendUserHandler replacement',
    'description' => 'TF BackendUserHandler replacement',
    'category' => 'example',
    'version' => '1.0.0',
    'state' => 'beta',
    'createDirs' => '',
    'clearCacheOnLoad' => 0,
    'author' => 'Stefan Bürk',
    'author_email' => 'stefan@buerk.tech',
    'author_company' => '',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.0-13.4.99',
            'json_response' => '*',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];

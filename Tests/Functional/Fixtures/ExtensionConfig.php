<?php

$deeplApiKey = (string)(getenv('DEEPL_API_KEY') ?? '');
if ($deeplApiKey === '') {
    $deeplHost = getenv('DEEPL_HOST') ?? 'localhost';
    $deeplPort = getenv('DEEPL_PORT') ?? '3000';
    $deeplScheme = getenv('DEEPL_SCHEME') ?? 'http';
    if ((bool)getenv('IS_DDEV_PROJECT')) {
        $deeplHost = 'ddev-' . (getenv('DDEV_SITENAME') ?? 'deepltranslate') . '-deeplmockserver';
        $deeplPort = '3000';
        $deeplScheme = 'http';
        defined('DEEPL_MOCKSERVER_USED') || define('DEEPL_MOCKSERVER_USED', true);
    }
    return [
        'EXTENSIONS' => [
            'wv_deepltranslate' => [
                'apiKey' => 'wv-deepltranslate-deepl-mockserver-api-key',
                'apiUrl' => $deeplScheme . '://' . $deeplHost . ':' . $deeplPort . '/v2/translate',
                'deeplFormality' => 'default',
                'googleapiKey' => '',
                'googleapiUrl' => 'https://translation.googleapis.com/language/translate/v2',
            ],
        ],
    ];
}

defined('DEEPL_MOCKSERVER_USED') || define('DEEPL_MOCKSERVER_USED', false);
return [
    'EXTENSIONS' => [
        'wv_deepltranslate' => [
            'apiKey' => $deeplApiKey,
            'apiUrl' => 'https://api-free.deepl.com/v2/translate',
            'deeplFormality' => 'default',
            'googleapiKey' => '',
            'googleapiUrl' => 'https://translation.googleapis.com/language/translate/v2',
        ],
    ],
];

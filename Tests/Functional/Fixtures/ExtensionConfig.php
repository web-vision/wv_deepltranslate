<?php

getenv('DEEPL_API_KEY') or die('Environment variable DEEPL_API_KEY is not set. Please set in local Context in ddev config.yaml');

return [
    'EXTENSIONS' => [
        'wv_deepltranslate' => [
            'apiKey' => getenv('DEEPL_API_KEY') ?? '',
            'apiUrl' => 'https://api-free.deepl.com/v2/translate',
            'deeplFormality' => 'default',
            'googleapiKey' => '',
            'googleapiUrl' => 'https://translation.googleapis.com/language/translate/v2',
        ],
    ],
];

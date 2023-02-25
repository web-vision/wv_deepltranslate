<?php

declare(strict_types=1);

return [
    \WebVision\WvDeepltranslate\Domain\Model\Settings::class => [
        'tableName' => 'tx_deepl_settings',
    ],
    \WebVision\WvDeepltranslate\Domain\Model\Language::class => [
        'tableName' => 'sys_language',
        'properties' => [
            'languageIsoCode' => [
                'fieldName' => 'language_isocode',
            ],
            'staticLangIsoCode' => [
                'fieldName' => 'static_lang_isocode',
            ],
            'createDate' => [
                'fieldName' => 'crdate',
            ],
        ],
    ],
];

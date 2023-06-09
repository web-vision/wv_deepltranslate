<?php

use WebVision\WvDeepltranslate\Form\Item\SiteConfigSupportedLanguageItemsProcFunc;

(static function (): void {
    $ll = function (string $identifier) {
        return 'LLL:EXT:wv_deepltranslate/Resources/Private/Language/locallang.xlf:' . $identifier;
    };

    $GLOBALS['SiteConfiguration']['site_language']['columns']['deeplTargetLanguage'] = [
        'label' => $ll('site_configuration.deepl.field.label'),
        'description' => $ll('site_configuration.deepl.field.description'),
        'config' => [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'itemsProcFunc' => SiteConfigSupportedLanguageItemsProcFunc::class . '->getSupportedLanguageForField',
            'items' => [],
            'minitems' => 0,
            'maxitems' => 1,
            'size' => 1,
        ],
    ];

    $GLOBALS['SiteConfiguration']['site_language']['palettes']['deepl'] = [
        'showitem' => 'deeplTargetLanguage',
    ];

    $GLOBALS['SiteConfiguration']['site_language']['types']['1']['showitem'] = str_replace(
        '--palette--;;default,',
        '--palette--;;default, --palette--;LLL:EXT:wv_deepltranslate/Resources/Private/Language/locallang.xlf:site_configuration.deepl.title;deepl,',
        $GLOBALS['SiteConfiguration']['site_language']['types']['1']['showitem']
    );
})();

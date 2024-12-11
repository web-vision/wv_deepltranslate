<?php

use WebVision\Deepltranslate\Core\Form\Item\SiteConfigSupportedLanguageItemsProcFunc;

(static function (): void {
    $ll = function (string $identifier) {
        return 'LLL:EXT:deepltranslate_core/Resources/Private/Language/locallang.xlf:' . $identifier;
    };

    $GLOBALS['SiteConfiguration']['site_language']['columns']['deeplTargetLanguage'] = [
        'label' => $ll('site_configuration.deepl.field.targetlanguage.label'),
        'description' => $ll('site_configuration.deepl.field.targetlanguage.description'),
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

    $GLOBALS['SiteConfiguration']['site_language']['columns']['deeplFormality'] = [
        'label' => $ll('site_configuration.deepl.field.formality.label'),
        'description' => $ll('site_configuration.deepl.field.formality.description'),
        'displayCond' => [
            'AND' => [
                'USER:' . \WebVision\Deepltranslate\Core\Form\User\HasFormalitySupport::class . '->checkFormalitySupport',
            ],
        ],
        'config' => [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'items' => [
                [
                    ((new \TYPO3\CMS\Core\Information\Typo3Version())->getMajorVersion() >= 12 ? 'label' : 0) => 'default',
                    ((new \TYPO3\CMS\Core\Information\Typo3Version())->getMajorVersion() >= 12 ? 'value' : 1) => 'default',
                ],
                [
                    ((new \TYPO3\CMS\Core\Information\Typo3Version())->getMajorVersion() >= 12 ? 'label' : 0) => 'more formal language',
                    ((new \TYPO3\CMS\Core\Information\Typo3Version())->getMajorVersion() >= 12 ? 'value' : 1) => 'more',
                ],
                [
                    ((new \TYPO3\CMS\Core\Information\Typo3Version())->getMajorVersion() >= 12 ? 'label' : 0) => 'more informal language',
                    ((new \TYPO3\CMS\Core\Information\Typo3Version())->getMajorVersion() >= 12 ? 'value' : 1) => 'less',
                ],
                [
                    ((new \TYPO3\CMS\Core\Information\Typo3Version())->getMajorVersion() >= 12 ? 'label' : 0) => 'prefer more language, fallback default',
                    ((new \TYPO3\CMS\Core\Information\Typo3Version())->getMajorVersion() >= 12 ? 'value' : 1) => 'prefer_more',
                ],
                [
                    ((new \TYPO3\CMS\Core\Information\Typo3Version())->getMajorVersion() >= 12 ? 'label' : 0) => 'prefer informal language, fallback default',
                    ((new \TYPO3\CMS\Core\Information\Typo3Version())->getMajorVersion() >= 12 ? 'value' : 1) => 'prefer_less',
                ],
            ],
            'minitems' => 0,
            'maxitems' => 1,
            'size' => 1,
        ],
    ];

    $GLOBALS['SiteConfiguration']['site_language']['palettes']['deepl'] = [
        'showitem' => 'deeplTargetLanguage, deeplFormality',
    ];

    $GLOBALS['SiteConfiguration']['site_language']['types']['1']['showitem'] = str_replace(
        '--palette--;;default,',
        '--palette--;;default, --palette--;LLL:EXT:deepltranslate_core/Resources/Private/Language/locallang.xlf:site_configuration.deepl.title;deepl,',
        $GLOBALS['SiteConfiguration']['site_language']['types']['1']['showitem']
    );
})();

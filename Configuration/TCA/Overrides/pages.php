<?php

if (!defined('TYPO3_MODE')) {
    die();
}

(static function (): void {
    $ll = function (string $languageKey) {
        return sprintf('LLL:EXT:wv_deepltranslate/Resources/Private/Language/locallang.xlf:%s', $languageKey);
    };

    $GLOBALS['TCA']['pages']['columns']['module']['config']['items'][] = [
        'DeepL Glossar',
        'wv_deepltranslate',
        'apps-pagetree-folder-contains-glossar',
    ];
    $GLOBALS['TCA']['pages']['ctrl']['typeicon_classes']['contains-glossar'] = 'apps-pagetree-folder-contains-glossar';

    $columns = [
        'tx_wvdeepltranslate_content_not_checked' => [
            'exclude' => 0,
            'l10n_display' => 'hideDiff',
            'displayCond' => 'FIELD:sys_language_uid:>:0',
            'label' => $ll('pages.tx_wvdeepltranslate_content_not_checked'),
            'config' => [
                'type' => 'check',
                'items' => [
                    [
                        $ll('traslated_with_deepl'),
                    ],
                ],
            ],
        ],
        'tx_wvdeepltranslate_translated_time' => [
            'exclude' => 0,
            'l10n_display' => 'hideDiff',
            'displayCond' => 'FIELD:sys_language_uid:>:0',
            'label' => $ll('pages.tx_wvdeepltranslate_translated_time'),
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'eval' => 'datetime',
                'readOnly' => true,
                'default' => 0,
            ],
        ],
    ];

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('pages', $columns);

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette(
        'pages',
        'deepl_translate',
        'tx_wvdeepltranslate_content_not_checked, tx_wvdeepltranslate_translated_time'
    );

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
        'pages',
        sprintf('--div--;%s,--palette--;;deepl_translate;', $ll('pages.deepl.tab.label')),
        '',
        'after:media'
    );
})();

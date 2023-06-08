<?php

defined('TYPO3') or die();

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

(static function (): void {
    $ll = function (string $languageKey) {
        return sprintf(
            'LLL:EXT:wv_deepltranslate/Resources/Private/Language/locallang.xlf:%s',
            $languageKey
        );
    };

    $GLOBALS['TCA']['pages']['columns']['module']['config']['items'][] = [
        ((new \TYPO3\CMS\Core\Information\Typo3Version())->getMajorVersion() >= 12 ? 'label' : 0) => 'DeepL Glossary',
        ((new \TYPO3\CMS\Core\Information\Typo3Version())->getMajorVersion() >= 12 ? 'value' : 1) => 'glossary',
        ((new \TYPO3\CMS\Core\Information\Typo3Version())->getMajorVersion() >= 12 ? 'icon' : 2) => 'apps-pagetree-folder-contains-glossary',
    ];
    $GLOBALS['TCA']['pages']['ctrl']['typeicon_classes']['contains-glossary']
        = 'apps-pagetree-folder-contains-glossary';

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
                        ((new \TYPO3\CMS\Core\Information\Typo3Version())->getMajorVersion() >= 12 ? 'label' : 0) => $ll('traslated_with_deepl'),
                    ],
                ],
            ],
        ],
        'glossary_information' => [
            'label' => $ll('pages.glossary_information'),
            'displayCond' => [
                'AND' => [
                    'FIELD:doktype:=:254',
                    'FIELD:module:=:glossary',
                ],
            ],
            'config' => [
                'type' => 'inline',
                'readOnly' => true,
                'foreign_table' => 'tx_wvdeepltranslate_glossary',
                'foreign_field' => 'pid',
            ],
        ],
    ];

    $columns['tx_wvdeepltranslate_translated_time'] = [
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
    ];
    if ((new \TYPO3\CMS\Core\Information\Typo3Version())->getMajorVersion() >= 12) {
        // 4.   https://review.typo3.org/c/Packages/TYPO3.CMS/+/74027
        //      https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/12.0/Feature-97232-NewTCATypeDatetime.html
        //      https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/12.0/Breaking-97358-RemovedEvalintFromTCATypeDatetime.html
        $columns['tx_wvdeepltranslate_translated_time'] = [
            'exclude' => 0,
            'l10n_display' => 'hideDiff',
            'displayCond' => 'FIELD:sys_language_uid:>:0',
            'label' => $ll('pages.tx_wvdeepltranslate_translated_time'),
            'config' => [
                'type' => 'datetime',
                'format' => 'datetime',
                'readOnly' => true,
                'default' => 0,
            ],
        ];
    }

    ExtensionManagementUtility::addTCAcolumns('pages', $columns);

    ExtensionManagementUtility::addFieldsToPalette(
        'pages',
        'deepl_translate',
        'tx_wvdeepltranslate_content_not_checked, tx_wvdeepltranslate_translated_time,glossary_information'
    );

    ExtensionManagementUtility::addToAllTCAtypes(
        'pages',
        sprintf('--div--;%s,--palette--;;deepl_translate;', $ll('pages.deepl.tab.label')),
        '',
        'after:language'
    );
})();

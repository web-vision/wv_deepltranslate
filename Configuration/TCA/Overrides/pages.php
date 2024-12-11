<?php

defined('TYPO3') or die();

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

(static function (): void {
    $GLOBALS['TCA']['pages']['columns']['module']['config']['items'][] = [
        'label' => 'DeepL Glossary',
        'value' => 'glossary',
        'icon' => 'apps-pagetree-folder-contains-glossary',
    ];
    $GLOBALS['TCA']['pages']['ctrl']['typeicon_classes']['contains-glossary']
        = 'apps-pagetree-folder-contains-glossary';

    $columns = [
        'tx_wvdeepltranslate_content_not_checked' => [
            'exclude' => 0,
            'l10n_display' => 'hideDiff',
            'displayCond' => 'FIELD:sys_language_uid:>:0',
            'label' => 'LLL:EXT:deepltranslate_core/Resources/Private/Language/locallang.xlf:pages.tx_wvdeepltranslate_content_not_checked',
            'config' => [
                'type' => 'check',
                'items' => [
                    [
                        'label' => 'LLL:EXT:deepltranslate_core/Resources/Private/Language/locallang.xlf:translated_with_deepl',
                    ],
                ],
            ],
        ],
        'glossary_information' => [
            'label' => 'LLL:EXT:deepltranslate_core/Resources/Private/Language/locallang.xlf:pages.glossary_information',
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
        'label' => 'LLL:EXT:deepltranslate_core/Resources/Private/Language/locallang.xlf:pages.tx_wvdeepltranslate_translated_time',
        'config' => [
            'type' => 'datetime',
            'format' => 'datetime',
            'readOnly' => true,
            'default' => 0,
        ],
    ];

    ExtensionManagementUtility::addTCAcolumns('pages', $columns);

    ExtensionManagementUtility::addFieldsToPalette(
        'pages',
        'deepl_translate',
        implode(',', [
            'tx_wvdeepltranslate_content_not_checked',
            'tx_wvdeepltranslate_translated_time',
            'glossary_information',
        ])
    );

    ExtensionManagementUtility::addToAllTCAtypes(
        'pages',
        sprintf('--div--;%s,--palette--;;deepl_translate;', 'LLL:EXT:deepltranslate_core/Resources/Private/Language/locallang.xlf:pages.deepl.tab.label'),
        '',
        'after:language'
    );
})();

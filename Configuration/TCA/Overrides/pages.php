<?php
if (!defined('TYPO3_MODE')) {
    die();
}

$GLOBALS['TCA']['pages']['columns']['module']['config']['items'][] = [
    'DeepL Glossar',
    'wv_deepltranslate',
    'apps-pagetree-folder-contains-glossar',
];
$GLOBALS['TCA']['pages']['ctrl']['typeicon_classes']['contains-glossar'] = 'apps-pagetree-folder-contains-glossar';

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
    'pages',
    [

        'tx_wvdeepltranslate_has_translated_content' => [
            'exclude' => 0,
            'displayCond' => 'USER:WebVision\\WvDeepltranslate\\Domain\\Repository\\PageRepository->hasDeeplTranslatedContent',
            'label' => 'LLL:EXT:wv_deepltranslate/Resources/Private/Language/locallang_db.xlf:tx_wvdeepltranslate_has_translated_content',
            'config' => [
               'type' => 'check',
               'readOnly' => true,
               'items' => [
                  [
                      'LLL:EXT:wv_deepltranslate/Resources/Private/Language/locallang_db.xlf:traslated_with_deepl',
                  ],
               ],
            ],
         ],

       'tx_wvdeepltranslate_translated_time' => [
          'exclude' => 0,
          'label' => 'LLL:EXT:wv_deepltranslate/Resources/Private/Language/locallang_db.xlf:tx_wvdeepltranslate_translated_time',
          'config' => [
            'type' => 'input',
            'renderType' => 'inputDateTime',
            'eval' => 'datetime',
            'readOnly' =>true,
            'default' => 0,
        ],
       ],
    ]
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette(
    'pages',
    'language',
    'tx_wvdeepltranslate_has_translated_content,tx_wvdeepltranslate_translated_time',
    ''
);

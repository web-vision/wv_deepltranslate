<?php

declare(strict_types=1);

return [
    'ctrl' => [
        'title' => 'LLL:EXT:wv_deepltranslate/Resources/Private/Language/locallang.xlf:glossary',
        'label' => 'glossary_name',
        'label_userFunc' => \WebVision\WvDeepltranslate\TCA\LanguageSelectorItemsProc::class . '->glossaryLabel',
        'iconfile' => 'EXT:wv_deepltranslate/Resources/Public/Icons/deepl.svg',
        'default_sortby' => 'uid',
        'tstamp' => 'tstamp',
        'descriptionColumn' => 'rowDescription',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'delete' => 'deleted',
        'versioningWS' => false,
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'searchFields' => 'glossary_name,glossary_id,entries',
    ],
    'inferface' => [
        'showRecordFieldList' => '',
        'maxDBListItems' => 20,
        'maxSingleDBListItems' => 100,
    ],
    'palettes' => [
        'lang' => [
            'description' => 'LLL:EXT:wv_deepltranslate/Resources/Private/Language/locallang.xlf:glossary.lang.palette.description',
            'showitem' => 'source_lang,target_lang',
        ],
        'deepl' => [
            'showitem' => 'glossary_lastsync,glossary_ready',
        ],
    ],
    'types' => [
        '1' => [
            'showitem' => '
            glossary_name,--palette--;;lang,entries,
            --div--;LLL:EXT:wv_deepltranslate/Resources/Private/Language/locallang.xlf:glossary.tab.sync,
            glossary_id,--palette--;;deepl',
        ],
    ],
    'columns' => [
        'glossary_id' => [
            'label' => 'LLL:EXT:wv_deepltranslate/Resources/Private/Language/locallang.xlf:glossary.glossary_id',
            'config' => [
                'type' => 'input',
                'readOnly' => true,
            ],
        ],
        'glossary_name' => [
            'label' => 'LLL:EXT:wv_deepltranslate/Resources/Private/Language/locallang.xlf:glossary.glossary_name',
            'config' => [
                'type' => 'input',
            ],
        ],
        'target_lang' => [
            'label' => 'LLL:EXT:wv_deepltranslate/Resources/Private/Language/locallang.xlf:glossary.target_lang',
            'displayCond' => 'FIELD:source_lang:REQ:true',
            'onChange' => 'reload',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['Please select', null],
                ],
                'itemsProcFunc' => \WebVision\WvDeepltranslate\TCA\LanguageSelectorItemsProc::class . '->getFieldsForTarget',
                'itemsProcConfig' => [
                    'source_lang' => 'tt_content',
                ],
            ],
        ],
        'source_lang' => [
            'label' => 'LLL:EXT:wv_deepltranslate/Resources/Private/Language/locallang.xlf:glossary.source_lang',
            'onChange' => 'reload',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['Please select', null],
                ],
                'itemsProcFunc' => \WebVision\WvDeepltranslate\TCA\LanguageSelectorItemsProc::class . '->getFieldsForSource',
            ],
        ],
        'entries' => [
            'label' => 'LLL:EXT:wv_deepltranslate/Resources/Private/Language/locallang.xlf:glossary.entries',
            'description' => 'LLL:EXT:wv_deepltranslate/Resources/Private/Language/locallang.xlf:glossary.entries.description',
            'displayCond' => [
                'AND' => [
                    'FIELD:source_lang:REQ:true',
                    'FIELD:target_lang:REQ:true',
                ],
            ],
            'config' => [
                'type' => 'inline',
                'foreign_table' => 'tx_wvdeepltranslate_glossaryentry',
                'foreign_field' => 'glossary',
                'appearance' => [
                    'dragdrop' => false,
                    'localize' => false,
                    'sort' => false,
                ],
            ],
        ],
        'glossary_lastsync' => [
            'label' => 'LLL:EXT:wv_deepltranslate/Resources/Private/Language/locallang.xlf:glossary.glossary_lastsync',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'eval' => 'datetime',
                'readOnly' => true,
            ],
        ],
        'glossary_ready' => [
            'label' => 'LLL:EXT:wv_deepltranslate/Resources/Private/Language/locallang.xlf:glossary.glossary_ready',
            'config' => [
                'type' => 'check',
                'readOnly' => true,
            ],
        ],
    ],
];

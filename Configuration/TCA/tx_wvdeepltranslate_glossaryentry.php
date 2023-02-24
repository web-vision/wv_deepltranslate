<?php

declare(strict_types=1);

return [
    'ctrl' => [
        'title' => 'LLL:EXT:wv_deepltranslate/Resources/Private/Language/locallang.xlf:glossaryentry',
        'label' => 'source',
        'label_alt' => 'target',
        'label_alt_force' => true,
        'label_userFunc' => \WebVision\WvDeepltranslate\TCA\EntryItemProcFunc::class . '->entryLabel',
        'iconfile' => 'EXT:wv_deepltranslate/Resources/Public/Icons/deepl.svg',
        'default_sortby' => 'source ASC',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'delete' => 'deleted',
        'hideTable' => true,
        'versioningWS' => false,
        'enablecolumns' => [
        ],
        'searchFields' => 'source,target',
    ],
    'inferface' => [
        'showRecordFieldList' => '',
        'maxDBListItems' => 20,
        'maxSingleDBListItems' => 100,
    ],
    'palettes' => [
        'entry' => [
            'showitem' => 'source,target',
        ],
    ],
    'types' => [
        '1' => [
            'showitem' => '--palette--;;entry',
        ],
    ],
    'columns' => [
        'source' => [
            'label' => 'LLL:EXT:wv_deepltranslate/Resources/Private/Language/locallang.xlf:entry.source',
            'config' => [
                'type' => 'input',
                'eval' => 'required',
            ],
        ],
        'target' => [
            'label' => 'LLL:EXT:wv_deepltranslate/Resources/Private/Language/locallang.xlf:entry.target',
            'config' => [
                'type' => 'input',
                'eval' => 'required',
            ],
        ],
    ],
];

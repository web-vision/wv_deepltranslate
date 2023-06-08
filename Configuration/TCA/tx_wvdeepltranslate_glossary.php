<?php

declare(strict_types=1);

return [
    'ctrl' => [
        'title' => 'LLL:EXT:wv_deepltranslate/Resources/Private/Language/locallang.xlf:glossary',
        'label' => 'glossary_name',
        'iconfile' => 'EXT:wv_deepltranslate/Resources/Public/Icons/deepl.svg',
        'default_sortby' => 'uid',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'delete' => 'deleted',
        'hideTable' => true,
        'versioningWS' => false,
        'enablecolumns' => [],
        'searchFields' => 'glossary_name,glossary_id,glossary_ready,glossary_lastsync',
    ],
    'inferface' => [
        'showRecordFieldList' => '',
        'maxDBListItems' => 20,
        'maxSingleDBListItems' => 100,
    ],
    'palettes' => [
        'deepl' => [
            'showitem' => 'glossary_name,--linebreak--,glossary_lastsync,glossary_ready',
        ],
    ],
    'types' => [
        '1' => [
            'showitem' => '
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
                'readOnly' => true,
            ],
        ],
        'glossary_lastsync' => [
            'label' => 'LLL:EXT:wv_deepltranslate/Resources/Private/Language/locallang.xlf:glossary.glossary_lastsync',
            // 4.   https://review.typo3.org/c/Packages/TYPO3.CMS/+/74027
            //      https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/12.0/Feature-97232-NewTCATypeDatetime.html
            //      https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/12.0/Breaking-97358-RemovedEvalintFromTCATypeDatetime.html
            'config' => (new \TYPO3\CMS\Core\Information\Typo3Version())->getMajorVersion() >= 12
                ? [
                    'type' => 'datetime',
                    'format' => 'datetime',
                    'readOnly' => true,
                ]
                : [
                    'type' => 'input',
                    'renderType' => 'inputDateTime',
                    'eval' => 'datetime',
                    'readOnly' => true,
                ]
            ,
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

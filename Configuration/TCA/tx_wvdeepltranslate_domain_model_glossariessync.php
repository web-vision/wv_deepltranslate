<?php

if (!defined('TYPO3_MODE')) {
    die();
}

$ll = 'LLL:EXT:wv_deepltranslate/Resources/Private/Language/locallang.xlf:';

return [
    'ctrl' => [
        'title' => 'Deepl Glossaries Sync',
        'label' => 'glossary_id',
        'label_alt' => 'source_lang, target_lang',
        'label_alt_force' => true,
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'iconfile' => 'EXT:wv_deepltranslate/Resources/Public/Icons/deepl.svg',
        'default_sortby' => 'crdate DESC',
        'delete' => 'deleted',
    ],
    'columns' => [
        'crdate' => [
            'label' => 'crdate',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'eval' => 'datetime',
            ],
        ],
        'tstamp' => [
            'label' => 'tstamp',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'eval' => 'datetime',
            ],
        ],
        'glossary_id' => [
            'label' => $ll . 'glossary.glossary_id',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
            ],
        ],
        'source_lang' => [
            'label' => $ll . 'glossary.source_lang',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
            ],
        ],
        'target_lang' => [
            'label' =>  $ll . 'glossary.target_lang',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
            ],
        ],
        'entries' => [
            'label' =>  $ll . 'glossary.entries',
            'config' => [
                'type' => 'text',
                'cols' => 40,
                'rows' => 15,
                'readOnly' => true,
            ],
        ],
    ],
    'types' => [
        0 => [
            'showitem' => 'glossary_id,source_lang,target_lang,entries',
        ],
    ],
];

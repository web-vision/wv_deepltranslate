<?php

if (!defined('TYPO3_MODE')) {
    die();
}

$ll = 'LLL:EXT:wv_deepltranslate/Resources/Private/Language/locallang_db.xlf:';

return [
    'ctrl' => [
        'title' => 'Deepl settings',
        'label' => 'uid',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'iconfile' => 'EXT:wv_deepltranslate/Resources/Public/Icons/deepl.svg',
        'hideTable' => true,
        'rootLevel' => true,
        'sortby' => 'sorting',
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
        'languages_assigned' => [
            'label' => 'Deepl language assignments',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'readOnly' => true,
            ],
        ],
    ],
    'types' => [
        0 => [
            'showitem' => 'languages_assigned',
        ],
    ],
];

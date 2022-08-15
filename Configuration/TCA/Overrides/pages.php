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

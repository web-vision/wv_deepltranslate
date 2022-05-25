<?php
if (!defined('TYPO3_MODE')) {
    die();
}

(function () {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
        'wv_deepltranslate',
        'Configuration/TypoScript',
        'wv_deepltranslate'
    );
})();

<?php
if (!defined('TYPO3_MODE')) {
    die();
}

(function () {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
        '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:wv_deepltranslate/Configuration/TsConfig/Page/pagetsconfig.tsconfig">'
    );

    //hook for translate content
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processTranslateToClass']['deepl']
        = \WebVision\WvDeepltranslate\Hooks\TranslateHook::class;
    //hook to checkModifyAccessList for editors
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['checkModifyAccessList']['deepl']
        = \WebVision\WvDeepltranslate\Hooks\TCEmainHook::class;
    //hook for overriding localization.js,recordlist.js and including deepl.css
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_pagerenderer.php']['render-preProcess']['deepl']
        = \WebVision\WvDeepltranslate\Hooks\TranslateHook::class . '->executePreRenderHook';

    //xclass localizationcontroller for localizeRecords() and process() action
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Backend\Controller\Page\LocalizationController::class] = [
        'className' => \WebVision\WvDeepltranslate\Override\LocalizationController::class,
    ];

    $typo3VersionArray = \TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionStringToArray(
        \TYPO3\CMS\Core\Utility\VersionNumberUtility::getCurrentTypo3Version()
    );


    if (version_compare($typo3VersionArray['version_main'], 11, '<')) {
        $databaseRecordClassName = \WebVision\WvDeepltranslate\Override\v10\DatabaseRecordList::class;
    } else {
        $databaseRecordClassName = \WebVision\WvDeepltranslate\Override\DatabaseRecordList::class;
    }

    //xclass databaserecordlist for rendering custom checkboxes to toggle deepl selection in recordlist
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Recordlist\RecordList\DatabaseRecordList::class] = [
        'className' => $databaseRecordClassName,
    ];

    if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('container')) {
        //xclass CommandMapPostProcessingHook for translating contents within containers
        if (class_exists(\B13\Container\Hooks\Datahandler\CommandMapPostProcessingHook::class)) {
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\B13\Container\Hooks\Datahandler\CommandMapPostProcessingHook::class] = [
                'className' => \WebVision\WvDeepltranslate\Override\CommandMapPostProcessingHook::class,
            ];
        }
    }

    if (TYPO3_MODE === 'BE') {
        $pageRenderer = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Page\PageRenderer::class);
        $pageRenderer->loadRequireJsModule('TYPO3/CMS/WvDeepltranslate/Localization');
    }
})();

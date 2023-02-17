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
    //hook for overriding recordlist.js and including deepl.css
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_pagerenderer.php']['render-preProcess']['deepl']
        = \WebVision\WvDeepltranslate\Hooks\DeeplResourcePageRenderHook::class . '->executePreRenderHook';

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processTranslateToClass'][] = \WebVision\WvDeepltranslate\Hooks\DataHandlerHook::class;
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = \WebVision\WvDeepltranslate\Hooks\DataHandlerHook::class;
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass'][] =  \WebVision\WvDeepltranslate\Hooks\DataHandlerHook::class;
    //xclass localizationcontroller for localizeRecords() and process() action
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Backend\Controller\Page\LocalizationController::class] = [
        'className' => \WebVision\WvDeepltranslate\Override\LocalizationController::class,
    ];

    $typo3VersionArray = \TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionStringToArray(
        \TYPO3\CMS\Core\Utility\VersionNumberUtility::getCurrentTypo3Version()
    );

    if (version_compare($typo3VersionArray['version_main'], 11, '<')) {
        $databaseRecordClassName = \WebVision\WvDeepltranslate\Override\v10\DatabaseRecordList::class;
        $recordListControllerClassName = \WebVision\WvDeepltranslate\Override\v10\DeeplRecordListController::class;
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Backend\View\PageLayoutView::class] = [
            'className' => \WebVision\WvDeepltranslate\Override\v10\DeeplPageLayoutView::class,
        ];
    } else {
        $databaseRecordClassName = \WebVision\WvDeepltranslate\Override\DatabaseRecordList::class;
        $recordListControllerClassName = \WebVision\WvDeepltranslate\Override\DeeplRecordListController::class;
    }

    //xclass databaserecordlist for rendering custom checkboxes to toggle deepl selection in recordlist
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Recordlist\RecordList\DatabaseRecordList::class] = [
        'className' => $databaseRecordClassName,
    ];

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Recordlist\Controller\RecordListController::class] = [
        'className' => $recordListControllerClassName,
    ];

    if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('container')) {
        //xclass CommandMapPostProcessingHook for translating contents within containers
        if (class_exists(\B13\Container\Hooks\Datahandler\CommandMapPostProcessingHook::class)) {
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\B13\Container\Hooks\Datahandler\CommandMapPostProcessingHook::class] = [
                'className' => \WebVision\WvDeepltranslate\Override\CommandMapPostProcessingHook::class,
            ];
        }
    }

    $icons = [
        'apps-pagetree-folder-contains-glossar' => 'deepl.svg',
        'actions-localize-deepl' => 'actions-localize-deepl.svg',
    ];
    $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
    foreach ($icons as $identifier => $path) {
        if (!$iconRegistry->isRegistered($identifier)) {
            $iconRegistry->registerIcon(
                $identifier,
                \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
                ['source' => 'EXT:wv_deepltranslate/Resources/Public/Icons/' . $path]
            );
        }
    }

    //add caching for DeepL API supported Languages
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['wvdeepltranslate']
        ??= [];
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['wvdeepltranslate']['backend']
        ??= \TYPO3\CMS\Core\Cache\Backend\SimpleFileBackend::class;
})();

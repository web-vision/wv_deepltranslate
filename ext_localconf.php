<?php

defined('TYPO3') or die();

(static function (): void {
    $typo3version = new \TYPO3\CMS\Core\Information\Typo3Version();

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
        '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:wv_deepltranslate/Configuration/TsConfig/Page/pagetsconfig.tsconfig">'
    );

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['wvDeepltranslate_updateGlossary']
    = \WebVision\WvDeepltranslate\Upgrades\GlossaryUpgradeWizard::class;

    //allowLanguageSynchronizationHook manipulates l10n_state
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][]
        = \WebVision\WvDeepltranslate\Hooks\AllowLanguageSynchronizationHook::class;

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][]
        = \WebVision\WvDeepltranslate\Hooks\Glossary\UpdatedGlossaryEntryTermHook::class;

    //hook for translate content
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processTranslateToClass']['deepl']
        = \WebVision\WvDeepltranslate\Hooks\TranslateHook::class;

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass']['deepl']
        = \WebVision\WvDeepltranslate\Hooks\TranslateHook::class;

    //hook to checkModifyAccessList for editors
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['checkModifyAccessList']['deepl']
        = \WebVision\WvDeepltranslate\Hooks\TCEmainHook::class;

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-all']['deepl-1675946132'] =
        \WebVision\WvDeepltranslate\Hooks\DeeplPreviewFlagGeneratePageHook::class . '->renderDeeplPreviewFlag';

    //xclass localizationcontroller for localizeRecords() and process() action
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Backend\Controller\Page\LocalizationController::class] = [
        'className' => \WebVision\WvDeepltranslate\Override\LocalizationController::class,
    ];

    if ($typo3version->getMajorVersion() >= 12) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Backend\Controller\RecordListController::class] = [
            'className' => \WebVision\WvDeepltranslate\Override\Core12\DeeplRecordListController::class,
        ];
        //xclass databaserecordlist for rendering custom checkboxes to toggle deepl selection in recordlist
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Backend\RecordList\DatabaseRecordList::class] = [
            'className' => \WebVision\WvDeepltranslate\Override\Core12\DatabaseRecordList::class,
        ];
    } else {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Recordlist\Controller\RecordListController::class] = [
            'className' => \WebVision\WvDeepltranslate\Override\Core11\DeeplRecordListController::class,
        ];
        //xclass databaserecordlist for rendering custom checkboxes to toggle deepl selection in recordlist
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Recordlist\RecordList\DatabaseRecordList::class] = [
            'className' => \WebVision\WvDeepltranslate\Override\Core11\DatabaseRecordList::class,
        ];

        // button hook changed to event, registration here only needed for v11
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['Backend\Template\Components\ButtonBar']['getButtonsHook']['wv_deepltranslate'] =
            \WebVision\WvDeepltranslate\Hooks\ButtonBarHook::class . '->getButtons';
    }

    if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('container')) {
        //xclass CommandMapPostProcessingHook for translating contents within containers
        if (class_exists(\B13\Container\Hooks\Datahandler\CommandMapPostProcessingHook::class)) {
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\B13\Container\Hooks\Datahandler\CommandMapPostProcessingHook::class] = [
                'className' => \WebVision\WvDeepltranslate\Override\CommandMapPostProcessingHook::class,
            ];
        }
    }

    // We need to provide the global backend javascript module instead of calling page-renderer here directly - which
    // cannot be done and checking the context (FE/BE) directly. Instantiating PageRenderer here directly would be
    // emitted an exception as the cache configuration manager cannot be retrieved in this early stage.
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_pagerenderer.php']['render-preProcess'][1684661135]
        = \WebVision\WvDeepltranslate\Hooks\PageRendererHook::class . '->renderPreProcess';

    //add caching for DeepL API-supported Languages
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['wvdeepltranslate']
        ??= [];
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['wvdeepltranslate']['backend']
        ??= \TYPO3\CMS\Core\Cache\Backend\SimpleFileBackend::class;
})();

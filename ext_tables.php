<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

(function () {
    $iconProviderConfiguration = [
        \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class => [
            'actions-localize-deepl' => ['source' => 'EXT:wv_deepltranslate/Resources/Public/Icons/actions-localize-deepl.svg'],
            'actions-localize-google' => ['source' => 'EXT:wv_deepltranslate/Resources/Public/Icons/actions-localize-google.svg'],
        ],
    ];

    $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
    foreach ($iconProviderConfiguration as $provider => $iconConfiguration) {
        foreach ($iconConfiguration as $identifier => $option) {
            $iconRegistry->registerIcon($identifier, $provider, $option);
        }
    }

    //register backend module
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
        'WebVision.WvDeepltranslate',
        'Deepl',
        '',
        '',
        [],
        [
            'icon'   => 'EXT:wv_deepltranslate/Resources/Public/Icons/deepl.svg',
            'access' => 'user,group',
            'labels' => 'LLL:EXT:wv_deepltranslate/Resources/Private/Language/locallang.xlf',
        ]
    );

    $typo3VersionArray = \TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionStringToArray(
        \TYPO3\CMS\Core\Utility\VersionNumberUtility::getCurrentTypo3Version()
    );

    if (version_compare($typo3VersionArray['version_main'], 10, '<')) {
        $actionsControllerArray = [
            'Settings' => 'index,saveSettings',
        ];
    } else {
        $actionsControllerArray = [
            \WebVision\WvDeepltranslate\Controller\SettingsController::class => 'index,saveSettings',
        ];
    }

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
        'WebVision.WvDeepltranslate',
        'Deepl',
        'Settings',
        '',
        $actionsControllerArray,
        [
            'icon'   => 'EXT:install/Resources/Public/Icons/module-install-settings.svg',
            'access' => 'user,group',
            'labels' => 'LLL:EXT:wv_deepltranslate/Resources/Private/Language/locallang_module_settings.xlf',
        ]
    );

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['locallangXMLOverride']['/typo3/sysext/backend/Resources/Private/Language/locallang_layout.xlf'] = 'EXT:wv_deepltranslate/Resources/Private/Language/locallang.xlf';

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_deepl_glossaries', 'EXT:wv_deepltranslate/Resources/Private/Language/locallang_csh_tx_deepl_glossaries.xlf');
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_wvdeepltranslate_domain_model_glossaries');

})();

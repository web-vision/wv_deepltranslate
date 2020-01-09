<?php
defined('TYPO3_MODE') || die('Access denied.');

call_user_func(
    function ($extKey) {
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($extKey, 'Configuration/TypoScript', 'deepltranslate');
    },
    $_EXTKEY
);

//icons to icon registry
$iconRegistry         = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
$deeplIconIdentifier = 'actions-localize-deepl';
$googleIconIdentifier = 'actions-localize-google';
$iconRegistry->registerIcon(
    $deeplIconIdentifier,
    \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
    ['source' => 'EXT:deepltranslate/Resources/Public/Icons/' . $deeplIconIdentifier . '.svg']
);

$iconRegistry->registerIcon(
    $googleIconIdentifier,
    \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
    ['source' => 'EXT:deepltranslate/Resources/Public/Icons/' . $googleIconIdentifier . '.svg']
);

//register backend module
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
    'WebVision.Deepltranslate',
    'Deepl',
    '',
    '',
    array(),
    array(
        'icon'   => 'EXT:deepltranslate/Resources/Public/Icons/deepl.svg',
        'access' => 'user,group',
        'labels' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang.xlf',
    )
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
    'WebVision.Deepltranslate',
    'Deepl',
    'Settings',
    '',
    array(
        'Settings' => 'index,saveSettings',
    ),
    array(
        'icon'   => 'EXT:deepltranslate/Resources/Public/Icons/settings.svg',
        'access' => 'user,group',
        'labels' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_module_settings.xlf',
    )
);

$GLOBALS['TYPO3_CONF_VARS']['SYS']['locallangXMLOverride']['/typo3/sysext/backend/Resources/Private/Language/locallang_layout.xlf'] = 'EXT:deepltranslate/Resources/Private/Language/locallang.xlf';

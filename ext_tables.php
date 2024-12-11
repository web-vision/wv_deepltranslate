<?php

use WebVision\Deepltranslate\Core\Access\AccessRegistry;

defined('TYPO3') or die();

(function () {
    $iconProviderConfiguration = [
        \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class => [
            'actions-localize-deepl' => ['source' => 'EXT:deepltranslate_core/Resources/Public/Icons/actions-localize-deepl.svg'],
            'actions-localize-google' => ['source' => 'EXT:deepltranslate_core/Resources/Public/Icons/actions-localize-google.svg'],
        ],
    ];

    $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
    foreach ($iconProviderConfiguration as $provider => $iconConfiguration) {
        foreach ($iconConfiguration as $identifier => $option) {
            $iconRegistry->registerIcon($identifier, $provider, $option);
        }
    }

    /** @var AccessRegistry $accessRegistry */
    $accessRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(AccessRegistry::class);
    $GLOBALS['TYPO3_CONF_VARS']['BE']['customPermOptions']['deepltranslate'] ??= [];
    $GLOBALS['TYPO3_CONF_VARS']['BE']['customPermOptions']['deepltranslate']['header'] = 'Deepl Translate Access';
    foreach ($accessRegistry->getAllAccess() as $access) {
        $GLOBALS['TYPO3_CONF_VARS']['BE']['customPermOptions']['deepltranslate']['items'][$access->getIdentifier()] = [
            $access->getTitle(),
            $access->getIconIdentifier(),
            $access->getDescription(),
        ];
    }
})();

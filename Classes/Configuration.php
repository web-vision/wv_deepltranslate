<?php

declare(strict_types=1);

namespace WebVision\Deepltranslate\Core;

use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class Configuration implements ConfigurationInterface, SingletonInterface
{
    private string $apiKey = '';

    /**
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     */
    public function __construct()
    {
        $extensionConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('deepltranslate_core');

        $this->apiKey = (string)($extensionConfiguration['apiKey'] ?? '');
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }
}

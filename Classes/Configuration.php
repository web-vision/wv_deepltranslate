<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate;

use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class Configuration implements ConfigurationInterface, SingletonInterface
{
    private string $apiKey = '';

    private string $formality = '';

    /**
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     */
    public function __construct()
    {
        $extensionConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('wv_deepltranslate');

        $this->apiKey = (string)($extensionConfiguration['apiKey'] ?? '');

        // In a future version, "Formality" should be moved to the SiteConfig
        $this->formality = (string)($extensionConfiguration['deeplFormality'] ?? 'default');
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    /**
     * @deprecated In a future version, "Formality" should be moved to the SiteConfig
     */
    public function getFormality(): string
    {
        return $this->formality;
    }
}

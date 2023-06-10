<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate;

use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

// @todo Make this class final and introduce a interface for it.
final class Configuration
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

        if (isset($extensionConfiguration['apiKey'])) {
            $this->apiKey = (string)$extensionConfiguration['apiKey'] ?? '';
        }

        // In einer zukünftigen version sollte "Formality" in die SiteConfig verschoben werden
        if (isset($extensionConfiguration['deeplFormality'])) {
            $this->formality = (string)$extensionConfiguration['deeplFormality'] ?? 'default';
        }
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function getFormality(): string
    {
        return $this->formality;
    }
}

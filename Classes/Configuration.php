<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Configuration
{
    /**
     * @var string
     */
    private $apiKey = '';

    /**
     * @var string
     */
    private $apiUrl = '';

    /**
     * @var string
     */
    private $formality = '';

    public function __construct()
    {
        $extensionConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('wv_deepltranslate');

        if (isset($extensionConfiguration['apiKey'])) {
            $this->apiKey = (string)$extensionConfiguration['apiKey'] ?? '';
        }

        if (isset($extensionConfiguration['apiUrl'])) {
            // api url free is default
            $this->apiUrl = (string)$extensionConfiguration['apiUrl'] ?? 'https://api-free.deepl.com/';
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

    public function getApiUrl(): string
    {
        $parseUrl = parse_url($this->apiUrl);

        return $parseUrl['host'] . ($parseUrl['port'] ? sprintf(':%s', $parseUrl['port']) : '') ?? '';
    }

    public function getFormality(): string
    {
        return $this->formality;
    }
}

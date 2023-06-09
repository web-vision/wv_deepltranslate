<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\Form\Item;

use WebVision\WvDeepltranslate\Service\DeeplService;

class SiteConfigSupportedLanguageItemsProcFunc
{
    private DeeplService $deeplService;

    public function __construct(
        DeeplService $deeplService
    ) {
        $this->deeplService = $deeplService;
    }

    public function getSupportedLanguageForField(array &$configuration)
    {
        $supportedLanguages = $this->deeplService->apiSupportedLanguages['target'];

        $configuration['items'][] = ['--- Select a Language ---', null];
        foreach ($supportedLanguages as $supportedLanguage) {
            $configuration['items'][] = [
                ((new \TYPO3\CMS\Core\Information\Typo3Version())->getMajorVersion() >= 12 ? 'label' : 0) => $supportedLanguage,
                ((new \TYPO3\CMS\Core\Information\Typo3Version())->getMajorVersion() >= 12 ? 'value' : 1) => $supportedLanguage,
            ];
        }
    }
}

<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\Form\Item;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use WebVision\WvDeepltranslate\Service\DeeplService;

class SiteConfigSupportedLanguageItemsProcFunc
{
    private DeeplService $deeplService;

    public function __construct(
        DeeplService $deeplService
    ){
        $this->deeplService = $deeplService;
    }

    public function getSupportedLanguageForField(array &$configuration)
    {
        $supportedLanguages = $this->deeplService->apiSupportedLanguages['target'];

        $configuration['items'][] = ['--- Select a Language ---', null];
        foreach ($supportedLanguages as $supportedLanguage) {
            $configuration['items'][] = [$supportedLanguage, $supportedLanguage];
        }
    }
}

<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\Form\Item;

use DeepL\Language;
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
        $supportedLanguages = $this->deeplService->getSupportLanguage()['target'];

        $configuration['items'][] = ['--- Select a Language ---', null];
        /** @var Language $supportedLanguage */
        foreach ($supportedLanguages as $supportedLanguage) {
            $configuration['items'][] = [
                ((new \TYPO3\CMS\Core\Information\Typo3Version())->getMajorVersion() >= 12 ? 'label' : 0) => $supportedLanguage->name,
                ((new \TYPO3\CMS\Core\Information\Typo3Version())->getMajorVersion() >= 12 ? 'value' : 1) => $supportedLanguage->code,
            ];
        }
    }
}

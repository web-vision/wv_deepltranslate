<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\Hooks;

use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Page\PageRenderer;

final class PageRendererHook
{
    /**
     * Ensure backend javascript module is required and loaded.
     *
     * @param array<string, mixed> $params
     */
    public function renderPreProcess(array $params, PageRenderer $pageRenderer): void
    {
        $typo3Version = new Typo3Version();
        if ($pageRenderer->getApplicationType() === 'BE' && $typo3Version->getMajorVersion() < 12) {
            $pageRenderer->loadRequireJsModule('TYPO3/CMS/WvDeepltranslate/Localization11');
        }
        // For some reason, the labels are not availible in JavaScript object `TYPO3.lang`. So we add them manually.
        $pageRenderer->addInlineLanguageLabelFile('EXT:wv_deepltranslate/Resources/Private/Language/locallang.xlf');
    }
}

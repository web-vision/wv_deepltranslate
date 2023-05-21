<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\Hooks;

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
        if ($pageRenderer->getApplicationType() === 'BE') {
            // @todo Validate and check if we need to use dedicated backend modules per core version or if a central
            //       one is compatible enough.
            $pageRenderer->loadRequireJsModule('TYPO3/CMS/WvDeepltranslate/Localization');
        }
    }
}

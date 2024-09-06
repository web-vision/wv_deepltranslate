<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\Event\Listener;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Backend\Backend\Event\SystemInformationToolbarCollectorEvent;
use TYPO3\CMS\Core\Localization\LanguageService;
use WebVision\WvDeepltranslate\Exception\ApiKeyNotSetException;
use WebVision\WvDeepltranslate\Service\UsageService;

class UsageToolBarEventListener implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private UsageService $usageService;

    public function __construct(
        UsageService $usageService
    ) {
        $this->usageService = $usageService;
    }

    public function __invoke(SystemInformationToolbarCollectorEvent $systemInformation): void
    {
        $character = null;
        try {
            $usage = $this->usageService->getCurrentUsage();
            // @todo Decide to handle empty UsageDetail later and add systeminformation with a default
            //       (no limit retrieved) instead of simply omitting it here now.
            if ($usage === null || $usage->character === null) {
                return;
            }
        } catch (ApiKeyNotSetException $exception) {
            // @todo Can be replaced with `$this->logger?->` when TYPO3 v11 and therefore PHP 7.4/8.0 support is dropped.
            if ($this->logger !== null) {
                $this->logger->error(sprintf('%s (%d)', $exception->getMessage(), $exception->getCode()));
            }
            return;
        }
        $systemInformation->getToolbarItem()->addSystemInformation(
            $this->getLanguageService()->sL('LLL:EXT:wv_deepltranslate/Resources/Private/Language/locallang.xlf:usages.toolbar-label'),
            sprintf(
                '%d / %d',
                $usage->character->count,
                $usage->character->limit
            ),
            'actions-localize-deepl',
        );
    }

    private function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}

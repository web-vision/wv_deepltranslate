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
            if($usage === null) {
                return;
            }

            $character = $usage->character;
        } catch (ApiKeyNotSetException $exception) {
            $this->logger->error(sprintf('%s (%d)', $exception->getMessage(), $exception->getCode()));
            return;
        }

        $subject = $this->getLanguageService()->sL('LLL:EXT:wv_deepltranslate/Resources/Private/Language/locallang.xlf:usages.toolbar-label');
        if ($character !== null) {
            $subject = sprintf(
                '%d / %d',
                $character->count,
                $character->limit
            );
        }

        $systemInformation->getToolbarItem()->addSystemInformation(
            $this->getLanguageService()->sL('LLL:EXT:wv_deepltranslate/Resources/Private/Language/locallang.xlf:usages.toolbar-label'),
            $subject,
            'actions-localize-deepl',
        );
    }

    private function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}

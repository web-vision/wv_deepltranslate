<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\Hooks;

use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use WebVision\WvDeepltranslate\Service\UsageService;

class UsageProcessAfterFinishHook
{
    private UsageService $usageService;

    public function __construct(
        UsageService $usageService
    ) {
        $this->usageService = $usageService;
    }

    public function processCmdmap_afterFinish(DataHandler $dataHandler): void
    {
        if (!isset($dataHandler->cmdmap['localization']['custom']['mode'])
            || $dataHandler->cmdmap['localization']['custom']['mode'] !== 'deepl'
        ) {
            return;
        }

        if (Environment::isCli() || Environment::getContext()->isTesting()) {
            return;
        }

        $usage = $this->usageService->getCurrentUsage();
        if ($usage === null || $usage->character === null) {
            return;
        }

        $title = $this->getLanguageService()->sL(
            'LLL:EXT:wv_deepltranslate/Resources/Private/Language/locallang.xlf:usages.flashmassage.title'
        );
        $message = $this->getLanguageService()->sL(
            'LLL:EXT:wv_deepltranslate/Resources/Private/Language/locallang.xlf:usages.flashmassage.limit.description'
        );

        $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
        $notificationQueue = $flashMessageService->getMessageQueueByIdentifier();

        $severity = -1;  // Info
        if ($this->usageService->isTranslateLimitExceeded()) {
            $severity = 1;  // Warning
        }

        $flashMessage = GeneralUtility::makeInstance(
            FlashMessage::class,
            sprintf($message, $this->formatNumber($usage->character->count), $this->formatNumber($usage->character->limit)),
            $title,
            $severity,
            true
        );

        $notificationQueue->addMessage($flashMessage);
    }

    private function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }

    private function formatNumber(int $number): string
    {
        return number_format($number, 0, ',', '.');
    }
}

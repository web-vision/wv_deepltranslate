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

        $severity = $this->determineSeverity($usage->character->count, $usage->character->limit);
        // Reduce noise - Don't bother editors with low quota usage messages
        if ($severity === FlashMessage::NOTICE) {
            return;
        }

        $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
        $notificationQueue = $flashMessageService->getMessageQueueByIdentifier();

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

    /**
     * Make large API limits easier to read
     *
     * @param int $number Any large integer - 5000000
     * @return string Formated, better readable string variant of the integer - 5.000.000
     */
    private function formatNumber(int $number): string
    {
        return number_format($number, 0, ',', '.');
    }

    /**
     * Calculate the message severity based on the quota usage rate
     *
     * @param int $characterCount Already translated characters in the current billing period
     * @param int $characterLimit Total character limit in the current billing period
     * @return int Severity level
     */
    private function determineSeverity(int $characterCount, int $characterLimit): int
    {
        $quotaUtilization = ($characterCount / $characterLimit) * 100;
        if ($quotaUtilization >= 100) {
            return FlashMessage::ERROR;
        }
        if ($quotaUtilization >= 98) {
            return FlashMessage::WARNING;
        }
        if ($quotaUtilization >= 90) {
            return FlashMessage::INFO;
        }
        return FlashMessage::NOTICE;
    }
}

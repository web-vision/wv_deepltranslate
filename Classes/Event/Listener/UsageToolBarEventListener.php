<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\Event\Listener;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Backend\Backend\Event\SystemInformationToolbarCollectorEvent;
use TYPO3\CMS\Backend\Toolbar\Enumeration\InformationStatus;
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
            if($usage === null || $usage->character === null) {
                return;
            }
        } catch (ApiKeyNotSetException $exception) {
            // @todo Can be replaced with `$this->logger?->` when TYPO3 v11 and therefore PHP 7.4/8.0 support is dropped.
            if ($this->logger !== null) {
                $this->logger->error(sprintf('%s (%d)', $exception->getMessage(), $exception->getCode()));
            }
            return;
        }

        $title = $this->getLanguageService()->sL(
            'LLL:EXT:wv_deepltranslate/Resources/Private/Language/locallang.xlf:usages.toolbar-label'
        );
        $message = $this->getLanguageService()->sL(
            'LLL:EXT:wv_deepltranslate/Resources/Private/Language/locallang.xlf:usages.toolbar.message'
        );

        $systemInformation->getToolbarItem()->addSystemInformation(
            $title,
            sprintf($message, $this->formatNumber($usage->character->count), $this->formatNumber($usage->character->limit)),
            'actions-localize-deepl',
            InformationStatus::STATUS_NOTICE,
        );
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
}

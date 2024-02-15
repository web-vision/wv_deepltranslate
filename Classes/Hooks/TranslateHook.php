<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\Hooks;

use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use WebVision\WvDeepltranslate\Exception\LanguageIsoCodeNotFoundException;
use WebVision\WvDeepltranslate\Exception\LanguageRecordNotFoundException;

class TranslateHook extends AbstractTranslateHook
{
    /**
     * @param array{uid: int} $languageRecord
     */
    public function processTranslateTo_copyAction(
        string &$content,
        array $languageRecord,
        DataHandler $dataHandler
    ): void {
        // Table Information are importen to find deepl configuration for site
        $tableName = self::$coreProcessorsInformation['tableName'];
        if ($tableName === null) {
            return;
        }

        // Record Information are importen to find deepl configuration for site
        $currentRecordId = self::$coreProcessorsInformation['id'];
        if ($currentRecordId === null) {
            return;
        }

        // Wenn you will translate file metadata use the extension "web-vision/deepltranslate-assets"
        if ($tableName === 'sys_file_metadata') {
            return;
        }

        // Translation mode not set to DeepL translate skip the translation
        if (self::$coreProcessorsInformation['mode'] !== 'deepl') {
            return;
        }

        $translatedContent = '';
        $targetLanguageRecord = [];

        $siteInformation = $this->languageService->getCurrentSite($tableName, $currentRecordId);

        if ($siteInformation === null) {
            return;
        }

        try {
            $sourceLanguageRecord = $this->languageService->getSourceLanguage(
                $siteInformation['site']
            );

            $targetLanguageRecord = $this->languageService->getTargetLanguage(
                $siteInformation['site'],
                (int)$languageRecord['uid']
            );

            $translatedContent = $this->translateContent(
                $content,
                $sourceLanguageRecord['language_isocode'],
                $targetLanguageRecord['language_isocode']
            );
        } catch (LanguageIsoCodeNotFoundException|LanguageRecordNotFoundException $e) {
            if (!Environment::isCli()) {
                // Flashmessage are only output in backend context
                $flashMessage = GeneralUtility::makeInstance(
                    FlashMessage::class,
                    $e->getMessage(),
                    '',
                    -1 // Info
                );
                GeneralUtility::makeInstance(FlashMessageService::class)
                    ->getMessageQueueByIdentifier()
                    ->addMessage($flashMessage);
            }
        }

        if ($translatedContent !== '') {
            if ($content !== ''
                && !empty($targetLanguageRecord)
            ) {
                $this->pageRepository->markPageAsTranslatedWithDeepl($siteInformation['pageUid'], $targetLanguageRecord);
            }
        }

        $content = $translatedContent ?: $content;
    }
}

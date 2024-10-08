<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\Hooks;

use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Site\SiteFinder;
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

        $pageId = $this->findCurrentParentPage($tableName, (int)$currentRecordId);
        try {
            $siteInformation = GeneralUtility::makeInstance(SiteFinder::class)->getSiteByPageId($pageId);
        } catch (SiteNotFoundException $e) {
            $siteInformation = null;
        }

        if ($siteInformation === null) {
            return;
        }

        try {
            $translatedContext = $this->createTranslateContext($content, (int)$languageRecord['uid'], $siteInformation);

            $translatedContent = $this->deeplService->translateContent($translatedContext);

            if ($translatedContent === '') {
                $this->flashMessages(
                    'Translation not successful', // ToDo use locallang label
                    '',
                    -1
                );
            }
        } catch (LanguageIsoCodeNotFoundException|LanguageRecordNotFoundException $e) {
            $this->flashMessages(
                $e->getMessage(),
                '',
                -1 // Info
            );
        }

        if ($translatedContent !== '' && $content !== '') {
            $this->pageRepository->markPageAsTranslatedWithDeepl($pageId, (int)$languageRecord['uid']);
        }

        $content = $translatedContent !== '' ? $translatedContent : $content;
    }
}

<?php

declare(strict_types = 1);

namespace WebVision\WvDeepltranslate\Hooks;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use WebVision\WvDeepltranslate\Domain\Repository\PageRepository;
use WebVision\WvDeepltranslate\Domain\Repository\SettingsRepository;
use WebVision\WvDeepltranslate\Service\DeeplService;
use WebVision\WvDeepltranslate\Service\GoogleTranslateService;
use WebVision\WvDeepltranslate\Service\LanguageService;
use WebVision\WvDeepltranslate\Utility\HtmlUtility;

class TranslateHook
{
    protected DeeplService $deeplService;

    protected GoogleTranslateService $googleService;

    protected SettingsRepository $deeplSettingsRepository;

    protected PageRepository $pageRepository;

    private LanguageService $langaugeService;

    public function __construct(
        ?SettingsRepository $settingsRepository = null,
        ?PageRepository $pageRepository = null,
        ?DeeplService $deeplService = null,
        ?GoogleTranslateService $googleService = null
    ) {
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->deeplSettingsRepository = $settingsRepository ?? $objectManager->get(SettingsRepository::class);
        $this->deeplService = $deeplService ?? $objectManager->get(DeeplService::class);
        $this->googleService = $googleService ?? $objectManager->get(GoogleTranslateService::class);
        $this->pageRepository = $pageRepository ?? GeneralUtility::makeInstance(PageRepository::class);
        $this->langaugeService = GeneralUtility::makeInstance(LanguageService::class);
    }

    /**
     * @param array{uid: int} $languageRecord
     */
    public function processTranslateTo_copyAction(string &$content, array $languageRecord, DataHandler $dataHandler): string
    {
        if (!isset($dataHandler->cmdmap['localization']['custom']['srcLanguageId'])) {
            $dataHandler->cmdmap['localization']['custom']['srcLanguageId'] = '';
        }

        $customMode = $dataHandler->cmdmap['localization']['custom']['mode'] ?? null;
        [$sourceLanguage,] = explode('-', $dataHandler->cmdmap['localization']['custom']['srcLanguageId']);

        //translation mode set to deepl or google translate
        if ($customMode === null) {
            return $content;
        }

        $tableName = '';
        $currentRecordId = '';

        foreach ($dataHandler->cmdmap as $key => $array) {
            $tableName = $key;
            foreach ($array as $innerkey => $innervalue) {
                $currentRecordId = $innerkey;
                break;
            }
            break;
        }

        $typo3VersionArray = VersionNumberUtility::convertVersionStringToArray(
            VersionNumberUtility::getCurrentTypo3Version()
        );

        if (version_compare((string)$typo3VersionArray['version_main'], '11', '<')) {
            $sourceLanguageRecord = BackendUtility::getRecord('sys_language', (int)$sourceLanguage);
            $targetLanguageRecord = BackendUtility::getRecord('sys_language', (int)$languageRecord['uid']);

            $targetLanguageMapping = $this->deeplSettingsRepository->getMappings($targetLanguageRecord['uid']);
            if ($targetLanguageMapping !== null) {
                $targetLanguageRecord['language_isocode'] = $targetLanguageMapping;
            }
            $sourceLanguageRecord = strtoupper($sourceLanguageRecord['language_isocode']);
        } else {
            $sourceLanguageRecord = $this->langaugeService->getSiteLanguageConfiguration(
                $tableName,
                (int)$currentRecordId,
                (int)$sourceLanguage,
                true
            );
            $targetLanguageRecord = $this->langaugeService->getSiteLanguageConfiguration(
                $tableName,
                (int)$currentRecordId,
                (int)$languageRecord['uid']
            );
        }

        $translatedContent = $this->translateContent(
            $content,
            $targetLanguageRecord,
            $customMode,
            $sourceLanguageRecord
        );

        if ($translatedContent !== '') {
            if ($customMode === 'deepl') {
                $currentPageRecord = BackendUtility::getRecord($tableName, (int)$currentRecordId);
                if ($currentPageRecord !== null && isset($currentPageRecord['uid'])) {
                    $this->pageRepository->markPageAsTranslatedWithDeepl($currentPageRecord['uid'], $targetLanguageRecord);
                }
            }

            // only the parameter reference is in use for content translate
            $content = $translatedContent;
        }

        return $content;
    }

    /**
     * These logics were outsourced to test them and later to resolve them in a service
     *
     * @param array{uid: int, language_isocode: string} $targetLanguageRecord
     * @param array{uid: int, language_isocode: string} $sourceLanguageRecord
     */
    public function translateContent(
        string $content,
        array $targetLanguageRecord,
        string $customMode,
        array $sourceLanguageRecord
    ): string {
        if (HtmlUtility::isHtml($content)) {
            $content = HtmlUtility::stripSpecificTags(['br'], $content);
        }

        // mode deepl
        if ($customMode == 'deepl') {
            //if target language and source language among supported languages
            $response = $this->deeplService->translateRequest(
                $content,
                $targetLanguageRecord['language_isocode'] ?? 'auto',
                $sourceLanguageRecord['language_isocode']
            );

            if (!empty($response) && isset($response->translations)) {
                foreach ($response->translations as $translation) {
                    if ($translation->text != '') {
                        $content = $translation->text;
                        break;
                    }
                }
            }
        } //mode google
        elseif ($customMode == 'google') {
            $response = $this->googleService->translate(
                $sourceLanguageRecord['language_isocode'],
                $targetLanguageRecord['language_isocode'],
                $content
            );

            if (!empty($response)) {
                if (HtmlUtility::isHtml($response)) {
                    $content = preg_replace('/\/\s/', '/', $response);
                    $content = preg_replace('/\>\s+/', '>', $content);
                }
            }
        }

        return $content;
    }
}

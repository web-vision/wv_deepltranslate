<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\Hooks;

use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use WebVision\WvDeepltranslate\Domain\Repository\PageRepository;
use WebVision\WvDeepltranslate\Domain\Repository\SettingsRepository;
use WebVision\WvDeepltranslate\Exception\LanguageIsoCodeNotFoundException;
use WebVision\WvDeepltranslate\Exception\LanguageRecordNotFoundException;
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

    private LanguageService $languageService;

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
        $this->languageService = GeneralUtility::makeInstance(LanguageService::class);
    }

    /**
     * @param array{uid: int} $languageRecord
     */
    public function processTranslateTo_copyAction(string &$content, array $languageRecord, DataHandler $dataHandler): string
    {
        $tableName = '';
        $currentRecordId = '';

        $cmdmap = $dataHandler->cmdmap;
        foreach ($cmdmap as $key => $array) {
            $tableName = $key;
            foreach ($array as $innerkey => $innervalue) {
                $currentRecordId = $innerkey;
                break;
            }
            break;
        }

        if (!isset($cmdmap['localization']['custom']['srcLanguageId'])) {
            $cmdmap['localization']['custom']['srcLanguageId'] = '';
        }

        $customMode = $cmdmap['localization']['custom']['mode'] ?? null;
        [$sourceLanguage,] = explode('-', (string)$cmdmap['localization']['custom']['srcLanguageId']);

        //translation mode set to deepl or google translate
        if ($customMode === null) {
            return $content;
        }

        $siteInformation = $this->languageService->getCurrentSite($tableName, $currentRecordId);

        $translatedContent = '';
        $targetLanguageRecord = [];

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
                $targetLanguageRecord,
                $customMode,
                $sourceLanguageRecord
            );
        } catch (LanguageIsoCodeNotFoundException|LanguageRecordNotFoundException $e) {
            $flashMessage = GeneralUtility::makeInstance(
                FlashMessage::class,
                $e->getMessage(),
                '',
                FlashMessage::INFO
            );
            GeneralUtility::makeInstance(FlashMessageService::class)
                ->getMessageQueueByIdentifier()
                ->addMessage($flashMessage);
        }

        if ($translatedContent !== '') {
            if ($content !== '' && $customMode === 'deepl' && !empty($targetLanguageRecord)) {
                if (isset($siteInformation['pageUid'])) {
                    $this->pageRepository->markPageAsTranslatedWithDeepl($siteInformation['pageUid'], $targetLanguageRecord);
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
        // mode deepl
        if ($customMode == 'deepl') {
            $response = $this->deeplService->translateRequest(
                $content,
                $targetLanguageRecord['language_isocode'],
                $sourceLanguageRecord['language_isocode']
            );

            if (!empty($response) && isset($response['translations'])) {
                foreach ($response['translations'] as $translation) {
                    if ($translation['text'] != '') {
                        $content = htmlspecialchars_decode($translation['text'], ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5);
                        break;
                    }
                }
            }
        } //mode google
        elseif ($customMode == 'google') {
            $response = $this->googleService->translate(
                $targetLanguageRecord['language_isocode'],
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

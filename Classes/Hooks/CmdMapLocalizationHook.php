<?php

declare(strict_types = 1);

namespace WebVision\WvDeepltranslate\Hooks;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use WebVision\WvDeepltranslate\Domain\Repository\GlossaryRepository;
use WebVision\WvDeepltranslate\Domain\Repository\PageRepository;
use WebVision\WvDeepltranslate\Exception\LanguageIsoCodeNotFoundException;
use WebVision\WvDeepltranslate\Service\DeeplService;
use WebVision\WvDeepltranslate\Service\LanguageService;

class CmdMapLocalizationHook
{
    private static bool $flashMessageSet = false;
    public function processCmdmap(
        string $command,
        string $table,
        int $id,
        int $value,
        bool &$commandIsProcessed,
        DataHandler $dataHandler,
        $pasteUpdate
    ): void {
        if ($command !== 'localize') {
            return;
        }
        $originalElement = BackendUtility::getRecord(
            $table,
            $id
        );
        $newId = $dataHandler->localize($table, $id, $value);
        $translatedElement = BackendUtility::getRecord(
            $table,
            $newId
        );
        $commandIsProcessed = true;
        $deeLTranslationEnabled = (bool)$GLOBALS['TCA'][$table]['ctrl']['deeplTranslation'] ?? false;

        if (!$deeLTranslationEnabled) {
            return;
        }
        $tableTCAColumns = $GLOBALS['TCA'][$table]['columns'];
        foreach ($tableTCAColumns as $field => $columnConfig) {
            if ($columnConfig['type'] === 'language') {
                $sysLanguageField = $field;
            }
        }
        // fallback, since 11.2 languageField is deprecated
        if (!isset($sysLanguageField)) {
            $sysLanguageField = $GLOBALS['TCA'][$table]['ctrl']['languageField'];
        }

        // no config for translation,
        // no language field set.
        // should NEVER appear here
        if (!isset($sysLanguageField)) {
            return;
        }

        $languageService = GeneralUtility::makeInstance(LanguageService::class);

        $siteInformation = $languageService->getCurrentSite($table, $id);

        try {
            $sourceLanguage = $languageService->getLanguage(
                $siteInformation['site'],
                (int)$originalElement[$sysLanguageField] ?? 0
            );
            $targetLanguage = $languageService->getLanguage(
                $siteInformation['site'],
                (int)$translatedElement[$sysLanguageField]
            );
        } catch (LanguageIsoCodeNotFoundException $e) {
            $targetLanguage = $languageService->getLanguage(
                $siteInformation['site'],
                (int)$translatedElement[$sysLanguageField],
                true
            );
            $this->cleanUpWithDefault(
                $table,
                $originalElement,
                $translatedElement,
                $dataHandler,
                $tableTCAColumns,
                $targetLanguage
            );
            return;
        }

        /* TODO Detect glossary here instead in service to avoid multiple calls
        $glossary = GeneralUtility::makeInstance(GlossaryRepository::class)
            ->detectGlossaryForTranslation($table, $id, $translatedElement['sys_language_uid']);
        */
        $deepLService = GeneralUtility::makeInstance(DeeplService::class);

        $deepLTranslated = false;
        $detectedSlugField = '';
        foreach ($translatedElement as $field => $value) {
            // reset slug to empty to auto create new with correct value
            if (
                isset($tableTCAColumns[$field])
                && isset($tableTCAColumns[$field]['config']['type'])
                && $tableTCAColumns[$field]['config']['type'] === 'slug'
            ) {
                $detectedSlugField = $field;
                continue;
            }
            if (
                !isset($tableTCAColumns[$field])
                || !isset($tableTCAColumns[$field]['l10n_mode'])
                || $tableTCAColumns[$field]['l10n_mode'] !== 'deepl'
            ) {
                continue;
            }
            $translatedContent = $deepLService->translateRequest(
                $originalElement[$field],
                $targetLanguage['language_isocode'],
                $sourceLanguage['language_isocode']
            );
            if (!empty($translatedContent) && isset($translatedContent['translations'])) {
                foreach ($translatedContent['translations'] as $translation) {
                    if ($translation['text'] != '') {
                        $value = $translation['text'] ?? $value;
                        $deepLTranslated = true;
                        break;
                    }
                }
            }
            $translatedElement[$field] = $value ?? $originalElement[$field];
        }
        if ($deepLTranslated) {
            // empty slug field to auto create
            if ($detectedSlugField !== '') {
                $translatedElement[$detectedSlugField] = '';
            }
            $data[$table][$translatedElement['uid']] = $translatedElement;
            $innerDataHandler = GeneralUtility::makeInstance(DataHandler::class);
            $innerDataHandler->start($data, []);
            $innerDataHandler->process_datamap();
            GeneralUtility::makeInstance(PageRepository::class)
                ->markPageAsTranslatedWithDeepl($siteInformation['pageUid'], $targetLanguage);
        }
    }

    private function cleanUpWithDefault(
        string $table,
        array $originalElement,
        array $translatedElement,
        DataHandler $dataHandler,
        array $tableTCA,
        array $targetLanguage
    ): void {
        // load some defaults
        $pageId = $table === 'pages' ? $originalElement['uid'] : $originalElement['pid'];
        $TSConfig = BackendUtility::getPagesTSconfig($pageId)['TCEMAIN.'] ?? [];
        $tableEntries = $dataHandler->getTableEntries($table, $TSConfig);
        if (!empty($TSConfig['translateToMessage']) && !($tableEntries['disablePrependAtCopy'] ?? false)) {
            $translateToMsg = $this->getLanguageService()->sL($TSConfig['translateToMessage']);
            $translateToMsg = @sprintf($translateToMsg, $targetLanguage['title']);
        }

        $detectedSlugField = '';
        foreach ($translatedElement as $field => $value) {
            if (
                isset($tableTCA[$field])
                && isset($tableTCA[$field]['config']['type'])
                && $tableTCA[$field]['config']['type'] === 'slug'
            ) {
                $detectedSlugField = $field;
                continue;
            }
            if (
                !isset($tableTCA[$field])
                || !isset($tableTCA[$field]['config']['type'])
                || (
                    $tableTCA[$field]['config']['type'] !== 'text'
                    && $tableTCA[$field]['config']['type'] !== 'input'
                )
            ) {
                continue;
            }
            if (!empty($translateToMsg) && !empty($originalElement[$field])) {
                $translatedElement[$field] = '[' . $translateToMsg . '] ' . $originalElement[$field] ?? '';
            } else {
                $translatedElement[$field] = $originalElement[$field] ?? '';
            }
        }

        if ($detectedSlugField !== '') {
            $translatedElement[$detectedSlugField] = '';
        }
        $data[$table][$translatedElement['uid']] = $translatedElement;
        $innerDataHandler = GeneralUtility::makeInstance(DataHandler::class);
        $innerDataHandler->start($data, []);
        $innerDataHandler->process_datamap();

        // check, if flashMessage was set before
        // needed for bulk translations to avoid
        // message appearing more than once
        if (self::$flashMessageSet) {
            return;
        }
        $flashMessage = GeneralUtility::makeInstance(
            FlashMessage::class,
            LocalizationUtility::translate(
                'automaticTranslation.fallback.message',
                'wv_deepltranslate'
            ),
            LocalizationUtility::translate(
                'automaticTranslation.fallback.title',
                'wv_deepltranslate'
            ),
            FlashMessage::INFO,
            true
        );
        GeneralUtility::makeInstance(FlashMessageService::class)
            ->getMessageQueueByIdentifier()
            ->enqueue($flashMessage);
        self::$flashMessageSet = true;

    }

    /**
     * @return \TYPO3\CMS\Core\Localization\LanguageService
     */
    protected function getLanguageService(): \TYPO3\CMS\Core\Localization\LanguageService
    {
        return $GLOBALS['LANG'];
    }
}

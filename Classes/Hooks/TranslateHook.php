<?php
declare(strict_types = 1);

namespace WebVision\WvDeepltranslate\Hooks;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2020 Ricky Mathew <ricky@web-vision.de>, web-vision GmbH
 *      Anu Bhuvanendran Nair <anu@web-vision.de>, web-vision GmbH
 *
 *  You may not remove or change the name of the author above. See:
 *  http://www.gnu.org/licenses/gpl-faq.html#IWantCredit
 *
 *  This script is part of the Typo3 project. The Typo3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  A copy is found in the textfile GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use WebVision\WvDeepltranslate\Domain\Repository\PageRepository;
use WebVision\WvDeepltranslate\Domain\Repository\SettingsRepository;
use WebVision\WvDeepltranslate\Service\DeeplService;
use WebVision\WvDeepltranslate\Service\GoogleTranslateService;
use WebVision\WvDeepltranslate\Utility\HtmlUtility;

class TranslateHook
{
    protected DeeplService $deeplService;

    protected GoogleTranslateService $googleService;

    protected SettingsRepository $deeplSettingsRepository;

    protected PageRepository $pageRepository;

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

        $translatedContent = $this->translateContent(
            $content,
            $languageRecord,
            $customMode,
            $sourceLanguage,
            $tableName,
            (int)$currentRecordId
        );

        if ($translatedContent !== '') {
            // only the parameter reference is in use for content translate
            $content = $translatedContent;
        }

        return $content;
    }

    /**
     * These logics were outsourced to test them and later to resolve them in a service
     */
    public function translateContent(
        string $content,
        array $targetLanguageRecord,
        string $customMode,
        ?string $sourceLanguage = null,
        string $tableName = '',
        ?int $currentRecordId = null
    ): string {
        $sourceLanguageCode = $sourceLanguage;
        $targetLanguage = BackendUtility::getRecord('sys_language', (int)$targetLanguageRecord['uid']);
        $sourceLanguage = BackendUtility::getRecord('sys_language', (int)$sourceLanguage);

        // get target language mapping if any
        if ($targetLanguage !== null) {
            $targetLanguageMapping = $this->deeplSettingsRepository->getMappings($targetLanguage['uid']);
            if ($targetLanguageMapping !== null) {
                $targetLanguage['language_isocode'] = $targetLanguageMapping;
            }
        }

        // Make good defaults
        // choose between default and autodetect
        $sourceLanguageIso = ($sourceLanguageCode == 'auto' ? null : 'EN');

        if ($sourceLanguage === null) {
            // current fallback to try to find the default language from the site configuration
            // when sys_language source not exist or not found
            if (!empty($tableName) && !empty($currentRecordId)) {
                $currentPageRecord = BackendUtility::getRecord($tableName, (int)$currentRecordId);
                $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
                try {
                    $site = $siteFinder->getSiteByPageId($currentPageRecord['pid']);
                    $sourceLanguageIso = strtoupper($site->getDefaultLanguage()->getTwoLetterIsoCode());
                    $targetLanguage['language_isocode'] = $site->getLanguageById($targetLanguageRecord['uid'])
                        ->getTwoLetterIsoCode();
                } catch (SiteNotFoundException $exception) {
                    // Ignore, use defaults
                }
            }
        } else {
            if (in_array($sourceLanguage['language_isocode'], $this->deeplService->apiSupportedLanguages)) {
                $sourceLanguageIso = strtoupper($sourceLanguage['language_isocode']);
            }
        }

        if (HtmlUtility::isHtml($content)) {
            $content = HtmlUtility::stripSpecificTags(['br'], $content);
        }

        // mode deepl
        if ($customMode == 'deepl') {
            $langSupportedByDeepLApi = in_array(
                strtoupper($targetLanguage['language_isocode'] ?? ''),
                $this->deeplService->apiSupportedLanguages
            );

            //if target language and source language among supported languages
            if ($langSupportedByDeepLApi) {
                $response = $this->deeplService->translateRequest(
                    $content,
                    $targetLanguage['language_isocode'],
                    $sourceLanguageIso
                );

                if (!empty($response) && isset($response->translations)) {
                    foreach ($response->translations as $translation) {
                        if ($translation->text != '') {
                            $content = $translation->text;
                            break;
                        }
                    }
                }
            }
        } //mode google
        elseif ($customMode == 'google') {
            $response = $this->googleService->translate($sourceLanguageIso, $targetLanguage['language_isocode'], $content);

            if (!empty($response)) {
                if (HtmlUtility::isHtml($response)) {
                    $content = preg_replace('/\/\s/', '/', $response);
                    $content = preg_replace('/\>\s+/', '>', $content);
                }
            }
        }

        if ($content !== '' && $customMode === 'deepl') {
            $currentPageRecord = BackendUtility::getRecord($tableName, (int)$currentRecordId);
            if ($currentPageRecord !== null && isset($currentPageRecord['uid'])) {
                $this->pageRepository->markPageAsTranslatedWithDeepl($currentPageRecord['uid'], $targetLanguage);
            }
        }

        return $content;
    }
}

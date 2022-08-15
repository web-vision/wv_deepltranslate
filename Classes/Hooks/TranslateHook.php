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
use WebVision\WvDeepltranslate\Domain\Repository\SettingsRepository;
use WebVision\WvDeepltranslate\Service\DeeplService;
use WebVision\WvDeepltranslate\Service\GoogleTranslateService;

class TranslateHook
{
    protected DeeplService $deeplService;

    protected GoogleTranslateService $googleService;

    protected SettingsRepository $deeplSettingsRepository;

    public function __construct(SettingsRepository $settingsRepository = null, DeeplService $deeplService = null, GoogleTranslateService $googleService = null)
    {
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->deeplSettingsRepository = $settingsRepository ?? $objectManager->get(SettingsRepository::class);
        $this->deeplService = $deeplService ?? $objectManager->get(DeeplService::class);
        $this->googleService = $googleService ?? $objectManager->get(GoogleTranslateService::class);
    }

    /**
     * processTranslateTo_copyAction hook
     *
     * @param array{uid: int} $languageRecord
     */
    public function processTranslateTo_copyAction(string &$content, array $languageRecord, DataHandler $dataHandler): string
    {
        $cmdmap = $dataHandler->cmdmap;
        foreach ($cmdmap as $key => $array) {
            $tablename = $key;
            foreach ($array as $innerkey => $innervalue) {
                $currectRecordId = $innerkey;
                break;
            }
            break;
        }
        if (!isset($cmdmap['localization']['custom']['srcLanguageId'])) {
            $cmdmap['localization']['custom']['srcLanguageId'] = '';
        }

        $customMode = $cmdmap['localization']['custom']['mode'] ?? null;

        //translation mode set to deepl or google translate
        if ($customMode !== null) {
            $langParam = explode('-', $cmdmap['localization']['custom']['srcLanguageId']);

            $sourceLanguageCode = $langParam[0];
            $targetLanguage = BackendUtility::getRecord('sys_language', $languageRecord['uid']);
            $sourceLanguage = BackendUtility::getRecord('sys_language', (int)$sourceLanguageCode);
            //get target language mapping if any
            if ($targetLanguage !== null) {
                $targetLanguageMapping = $this->deeplSettingsRepository->getMappings($targetLanguage['uid']);
            }
            if ($targetLanguageMapping !== null) {
                $targetLanguage['language_isocode'] = $targetLanguageMapping;
            }

            if ($sourceLanguage === null) {
                // Make good defaults
                $sourceLanguageIso = 'en';
                //choose between default and autodetect
                $deeplSourceIso = ($sourceLanguageCode == 'auto' ? null : 'EN');

                // Try to find the default language from the site configuration
                if (isset($tablename) && isset($currectRecordId)) {
                    $currentRecord = BackendUtility::getRecord($tablename, (int)$currectRecordId);
                    $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);

                    try {
                        $site = $siteFinder->getSiteByPageId($currentRecord['pid']);
                        $language = $site->getDefaultLanguage();
                        $sourceLanguageIso = strtolower($language->getTwoLetterIsoCode());
                        $targetLanguage = $site->getLanguageById($languageRecord['uid']);
                        $targetLanguageIso = $targetLanguage->getTwoLetterIsoCode();

                        if ($sourceLanguageCode !== 'auto') {
                            $deeplSourceIso = strtoupper($sourceLanguageIso);
                        }
                    } catch (SiteNotFoundException $exception) {
                        // Ignore, use defaults
                    }
                }
            } else {
                $sourceLanguageMapping = $this->deeplSettingsRepository->getMappings($sourceLanguage['uid']);
                if ($sourceLanguageMapping != null) {
                    $sourceLanguage['language_isocode'] = $sourceLanguageMapping;
                }
                $sourceLanguageIso = $sourceLanguage['language_isocode'];
                $deeplSourceIso = $sourceLanguageIso;
            }
            if ($this->isHtml($content)) {
                $content = $this->stripSpecificTags(['br'], $content);
            }

            // mode deepl
            if ($customMode == 'deepl') {
                $langSupportedByDeepLApi = in_array(strtoupper($targetLanguageIso), $this->deeplService->apiSupportedLanguages);
                //if target language and source language among supported languages
                if ($langSupportedByDeepLApi) {

                    $response = $this->deeplService->translateRequest($content, $targetLanguageIso, $sourceLanguageIso);

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

                $response = $this->googleService->translate($deeplSourceIso, $targetLanguageIso, $content);

                if (!empty($response)) {
                    if ($this->isHtml($response)) {
                        $content = preg_replace('/\/\s/', '/', $response);
                        $content = preg_replace('/\>\s+/', '>', $content);
                    } else {
                        $content = $response;
                    }
                }
            }
        }

        return $content;
    }

    /**
     * Execute PreRenderHook for possible manipulation:
     * Add deepl.css,overrides localization.js
     *
     * @param array[] $hook
     */
    public function executePreRenderHook(array &$hook): void
    {
        //assets are only needed in BE context
        if (TYPO3_MODE == 'BE') {
            //include deepl.css
            if (is_array($hook['cssFiles'])) {
                $hook['cssFiles']['/typo3conf/ext/wv_deepltranslate/Resources/Public/Css/deepl-min.css'] = [
                    'file' => '/typo3conf/ext/wv_deepltranslate/Resources/Public/Css/deepl-min.css',
                    'rel' => 'stylesheet',
                    'media' => 'all',
                    'title' => '',
                    'compress' => true,
                    'forceOnTop' => false,
                    'allWrap' => '',
                    'excludeFromConcatenation' => false,
                    'splitChar' => '|',
                ];
            }

            //inline js for adding deepl button on records list.
            $deeplButton = "function deeplTranslate(a,b){ $('#deepl-translation-enable-' + b).parent().parent().siblings().each(function() { var testing = $( this ).attr( 'href' ); if(document.getElementById('deepl-translation-enable-' + b).checked == true){ var newUrl = $( this ).attr( 'href' , testing + '&cmd[localization][custom][mode]=deepl'); } else { var newUrl = $( this ).attr( 'href' , testing + '&cmd[localization][custom][mode]=deepl'); } }); }";
            if (isset($hook['jsInline']['RecordListInlineJS']['code'])) {
                $hook['jsInline']['RecordListInlineJS']['code'] .= $deeplButton;
            } else {
                $hook['jsInline']['RecordListInlineJS']['code'] = $deeplButton;
            }
        }
    }

    /**
     * check whether the string contains html
     *
     * @param string $string
     */
    public function isHtml(string $string): bool
    {
        return preg_match('/<[^<]+>/', $string, $m) != 0;
    }

    /**
     * stripoff the tags provided
     *
     * @param string[] $tags
     */
    public function stripSpecificTags(array $tags, string $content): string
    {
        foreach ($tags as $tag) {
            $content = preg_replace('/<\\/?' . $tag . '(.|\\s)*?>/', '', $content);
        }

        return $content;
    }
}

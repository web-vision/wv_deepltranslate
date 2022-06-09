<?php

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

use WebVision\WvDeepltranslate\Domain\Repository\DeeplSettingsRepository;
use WebVision\WvDeepltranslate\Service\DeeplService;
use WebVision\WvDeepltranslate\Service\GoogleTranslateService;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class TranslateHook
{

    /**
     * @var \WebVision\WvDeepltranslate\Service\DeeplService
     */
    protected $deeplService;

    /**
     * @var \WebVision\WvDeepltranslate\Service\GoogleTranslateService
     */
    protected $googleService;

    /**
     * @var \WebVision\WvDeepltranslate\Domain\Repository\DeeplSettingsRepository
     * @inject
     */
    protected $deeplSettingsRepository;

    /**
     * Description
     * @return type
     */
    public function __construct()
    {
        $this->deeplService = GeneralUtility::makeInstance(DeeplService::class);
        $this->googleService = GeneralUtility::makeInstance(GoogleTranslateService::class);
        $this->deeplSettingsRepository = GeneralUtility::makeInstance(DeeplSettingsRepository::class);
    }

    /**
     * processTranslateTo_copyAction hook
     * @param type &$content
     * @param type $languageRecord
     * @param type $dataHandler
     * @return string
     */
    public function processTranslateTo_copyAction(&$content, $languageRecord, $dataHandler)
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
        $customMode = preg_replace('/^localize(deepl|google)(?:auto)?$/', '\1', $_GET['action']);
        if ($customMode === 'deepl' || $customMode === 'google') {
            $targetLanguage = BackendUtility::getRecord('sys_language', $languageRecord['uid']);
            //get target language mapping if any
            $targetLanguageMapping = $this->deeplSettingsRepository->getMappings($targetLanguage['uid']);
            if ($targetLanguageMapping != null) {
                $targetLanguage['language_isocode'] = $targetLanguageMapping;
            }

            [$sourceLanguageCode] = explode('-', $_GET['srcLanguageId']);
            if ($sourceLanguageCode === 'auto') {
                $deeplSourceIso = 'EN';
                // Try to find the default language from the site configuration
                if (isset($tablename) && isset($currectRecordId)) {
                    $currentRecord = BackendUtility::getRecord($tablename, (int)$currectRecordId);
                    $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
                    try {
                        $site = $siteFinder->getSiteByPageId($currentRecord['pid']);
                        $language = $site->getDefaultLanguage();
                        $deeplSourceIso = strtoupper($language->getTwoLetterIsoCode());
                    } catch (SiteNotFoundException $exception) {
                        // Ignore, use defaults
                    }
                }
            } else {
                $sourceLanguage = BackendUtility::getRecord('sys_language', (int)$sourceLanguageCode);
                $sourceLanguageMapping = $this->deeplSettingsRepository->getMappings($sourceLanguage['uid']);
                if ($sourceLanguageMapping != null) {
                    $sourceLanguage['language_isocode'] = $sourceLanguageMapping;
                }
                $deeplSourceIso = $sourceLanguage['language_isocode'];
            }
            if ($this->isHtml($content)) {
                $content = $this->stripSpecificTags(['br'], $content);
            }

            if ($customMode == 'deepl') {
                //if target language and source language among supported languages
                if (in_array(strtoupper($targetLanguage['language_isocode']), $this->deeplService->apiSupportedLanguages)) {
                    if ($tablename == 'tt_content') {
                        $response = $this->deeplService->translateRequest($content, $targetLanguage['language_isocode'], $deeplSourceIso);
                    } else {
                        $response = $this->deeplService->translateRequest($content, $targetLanguage['language_isocode'], $sourceLanguage['language_isocode']);
                    }
                    if (!empty($response) && isset($response->translations)) {
                        foreach ($response->translations as $translation) {
                            if ($translation->text != '') {
                                $content = $translation->text;
                                break;
                            }
                        }
                    }
                }
            } elseif ($customMode == 'google') {
                if ($tablename == 'tt_content') {
                    $response = $this->googleService->translate($deeplSourceIso, $targetLanguage['language_isocode'], $content);
                } else {
                    $response = $this->googleService->translate($content, $targetLanguage['language_isocode'], $content);
                }
                if (!empty($response)) {
                    if ($this->isHtml($response)) {
                        $content = preg_replace('/\/\s/', '/', $response);
                        $content = preg_replace('/\>\s+/', '>', $content);
                    } else {
                        $content = $response;
                    }
                }
            }
            //
        }
    }

    /**
     * Execute PreRenderHook for possible manipulation:
     * Add deepl.css,overrides localization.js
     */
    public function executePreRenderHook(&$hook)
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
            if (isset($hook['jsInline']['RecordListInlineJS']['code'])){
                $hook['jsInline']['RecordListInlineJS']['code'] .= $deeplButton;
            }else{
                $hook['jsInline']['RecordListInlineJS']['code'] = $deeplButton;
            }
        }
    }

    /**
     * check whether the string contains html
     * @param type $string
     * @return boolean
     */
    public function isHtml($string)
    {
        return preg_match("/<[^<]+>/", $string, $m) != 0;
    }

    /**
     * stripoff the tags provided
     * @param type $tags
     * @return string
     */
    public function stripSpecificTags($tags, $content)
    {
        foreach ($tags as $tag) {
            $content = preg_replace("/<\\/?" . $tag . "(.|\\s)*?>/", '', $content);
        }
        return $content;
    }
}

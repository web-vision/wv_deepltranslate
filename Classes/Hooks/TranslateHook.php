<?php
namespace PITS\Deepltranslate\Hooks;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2018 Ricky Mathew <ricky.mk@pitsolutions.com>, PIT Solutions
 *      Anu Bhuvanendran Nair <anu.bn@pitsolutions.com>, PIT Solutions
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

use PITS\Deepltranslate\Domain\Repository\DeeplSettingsRepository;
use PITS\Deepltranslate\Service\DeeplService;
use PITS\Deepltranslate\Service\GoogleTranslateService;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class TranslateHook
{

    /**
     * @var \PITS\Deepltranslate\Service\DeeplService
     */
    protected $deeplService;

    /**
     * @var \PITS\Deepltranslate\Service\GoogleTranslateService
     */
    protected $googleService;

    /**
     * @var \PITS\Deepltranslate\Domain\Repository\DeeplSettingsRepository
     * @inject
     */
    protected $deeplSettingsRepository;

    /**
     * Description
     * @return type
     */
    public function __construct()
    {
        $this->deeplService            = GeneralUtility::makeInstance(DeeplService::class);
        $this->googleService           = GeneralUtility::makeInstance(GoogleTranslateService::class);
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
        $customMode = $cmdmap['localization']['custom']['mode'];
        //translation mode set to deepl or google translate
        if (!is_null($customMode)) {
            $langParam          = explode('-', $cmdmap['localization']['custom']['srcLanguageId']);
            $sourceLanguageCode = $langParam[0];
            $targetLanguage     = BackendUtility::getRecord('sys_language', $languageRecord['uid']);
            $sourceLanguage     = BackendUtility::getRecord('sys_language', (int) $sourceLanguageCode);
            //get target language mapping if any
            $targetLanguageMapping = $this->deeplSettingsRepository->getMappings($targetLanguage['uid']);
            if ($targetLanguageMapping != null) {
                $targetLanguage['language_isocode'] = $targetLanguageMapping;
            }

            if ($sourceLanguage == null) {
                $sourceLanguageIso = 'en';
                //choose between default and autodetect
                $deeplSourceIso = ($sourceLanguageCode == 'auto' ? null : 'EN');
            } else {
                $sourceLanguageMapping = $this->deeplSettingsRepository->getMappings($sourceLanguage['uid']);
                if ($sourceLanguageMapping != null) {
                    $sourceLanguage['language_isocode'] = $sourceLanguageMapping;
                }
                $sourceLanguageIso = $sourceLanguage['language_isocode'];
                $deeplSourceIso    = $sourceLanguageIso;
            }
            if ($this->isHtml($content)) {
                $content = $this->stripSpecificTags(['br'], $content);
            }
            //mode deepl
            if ($customMode == 'deepl') {
                //if target language and source language among supported languages
                if (in_array(strtoupper($targetLanguage['language_isocode']), $this->deeplService->apiSupportedLanguages)) {

                    if ($tablename == 'tt_content') {
                        $response = $this->deeplService->translateRequest($content, $targetLanguage['language_isocode'], $deeplSourceIso);

                    } else {
                        $currentRecord     = BackendUtility::getRecord($tablename, (int) $currectRecordId);
                        $selectedTCAvalues = $this->getTemplateValues($currentRecord, $tablename, $field, $content);

                        if (!empty($selectedTCAvalues)) {
                            $response = $this->deeplService->translateRequest($selectedTCAvalues, $targetLanguage['language_isocode'], $sourceLanguage['language_isocode']);
                        }
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
            }
            //mode google
            elseif ($customMode == 'google') {
                if ($tablename == 'tt_content') {
                    $response = $this->googleService->translate($deeplSourceIso, $targetLanguage['language_isocode'], $content);

                } else {
                    $currentRecord     = BackendUtility::getRecord($tablename, (int) $currectRecordId);
                    $selectedTCAvalues = $this->getTemplateValues($currentRecord, $tablename, $field, $content);

                    if (!empty($selectedTCAvalues)) {
                        $response = $this->googleService->translate($sourceLanguage['language_isocode'], $targetLanguage['language_isocode'], $selectedTCAvalues);
                    }
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
        //include deepl.css
        if (is_array($hook['cssFiles'])) {
            $hook['cssFiles']['/typo3conf/ext/deepltranslate/Resources/Public/Css/deepl-min.css'] = [
                'file'                     => '/typo3conf/ext/deepltranslate/Resources/Public/Css/deepl-min.css',
                'rel'                      => 'stylesheet',
                'media'                    => 'all',
                'title'                    => '',
                'compress'                 => true,
                'forceOnTop'               => false,
                'allWrap'                  => '',
                'excludeFromConcatenation' => false,
                'splitChar'                => '|',
            ];
        }
        //override Localization.js
        if (is_array($hook['jsInline']['RequireJS-Module-TYPO3/CMS/Backend/Localization'])) {
            $hook['jsInline']['RequireJS-Module-TYPO3/CMS/Backend/Localization']['code'] = 'require(["TYPO3/CMS/Deepltranslate/Localization"]);';
        }
        //inline js for adding deepl button on records list.
        if (TYPO3_MODE == 'BE') {
          $hook['jsInline']['RecordListInlineJS']['code'] .= "function deeplTranslate(a,b){ $('#deepl-translation-enable-' + b).parent().parent().siblings().each(function() { var testing = $( this ).attr( 'href' ); if(document.getElementById('deepl-translation-enable-' + b).checked == true){ var newUrl = $( this ).attr( 'href' , testing + '&cmd[localization][custom][mode]=deepl'); } else { var newUrl = $( this ).attr( 'href' , testing + '&cmd[localization][custom][mode]=deepl'); } }); }";
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

    /**
     * Returns default content of records according to the typoscript setting from typoscript
     * @param array $recorddata
     * @param string $table
     * @param string $field
     * @return void
     */
    public function getTemplateValues($recorddata, $table, $field, $content)
    {
        $rootLineUtility = GeneralUtility::makeInstance('TYPO3\CMS\Core\Utility\RootlineUtility',$recorddata['pid']);
        $rootLine = $rootLineUtility->get();
        $TSObj           = GeneralUtility::makeInstance('TYPO3\CMS\Core\TypoScript\TemplateService');
        $TSObj->tt_track = 0;
        $TSObj->init();
        $TSObj->runThroughTemplates($rootLine);
        $TSObj->generateConfig();
        if ($table != '') {
            $fieldlist = $TSObj->setup['plugin.'][$table . '.']['translatableTCAvalues'];
            if ($fieldlist != null && strpos($fieldlist, $field) !== false) {
                $value = $this->deeplSettingsRepository->getRecordField($table, $field, $recorddata);
            } else {
                return $content;
            }
        }
    }

}

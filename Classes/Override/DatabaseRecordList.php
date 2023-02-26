<?php

namespace WebVision\WvDeepltranslate\Override;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use WebVision\WvDeepltranslate\Utility\DeeplBackendUtility;

/**
 * Class for rendering of Web>List module
 */
class DatabaseRecordList extends \TYPO3\CMS\Recordlist\RecordList\DatabaseRecordList
{
    /**
     * Creates the localization panel
     *
     * @param string $table The table
     * @param mixed[] $row The record for which to make the localization panel.
     * @param array[] $translations
     * @return string
     */
    public function makeLocalizationPanel($table, $row, array $translations): string
    {
        $out = parent::makeLocalizationPanel($table, $row, $translations);

        if ($out) {
            if (!DeeplBackendUtility::isDeeplApiKeySet()) {
                return $out;
            }

            // glossaries should not be auto translated by DeepL
            if ($table === 'tx_wvdeepltranslate_glossaryentry') {
                return $out;
            }

            $pageId = (int)($table === 'pages' ? $row['uid'] : $row['pid']);
            // All records excluding pages
            $possibleTranslations = $this->possibleTranslations;
            if ($table === 'pages') {
                // Calculate possible translations for pages
                $possibleTranslations = array_map(static fn ($siteLanguage) => $siteLanguage->getLanguageId(), $this->languagesAllowedForUser);
                $possibleTranslations = array_filter($possibleTranslations, static fn ($languageUid) => $languageUid > 0);
            }
            $languageInformation = $this->translateTools->getSystemLanguages($pageId);
            foreach ($possibleTranslations as $lUid_OnPage) {
                if ($this->isEditable($table)
                    && !$this->isRecordDeletePlaceholder($row)
                    && !isset($translations[$lUid_OnPage])
                    && $this->getBackendUserAuthentication()->checkLanguageAccess($lUid_OnPage)
                    && DeeplBackendUtility::checkCanBeTranslated($pageId, $lUid_OnPage)
                ) {
                    $out .= DeeplBackendUtility::buildTranslateButton(
                        $table,
                        $row['uid'],
                        $lUid_OnPage,
                        $this->listURL(),
                        $languageInformation[$lUid_OnPage]['title'],
                        $languageInformation[$lUid_OnPage]['flagIcon']
                    );
                }
            }
        }

        return $out;
    }
}

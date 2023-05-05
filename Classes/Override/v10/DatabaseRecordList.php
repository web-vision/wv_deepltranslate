<?php

namespace WebVision\WvDeepltranslate\Override\v10;

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

use TYPO3\CMS\Backend\Utility\BackendUtility;
use WebVision\WvDeepltranslate\Utility\DeeplBackendUtility;

/**
 * Compatible with v9 and v10
 *
 * Class for rendering of Web>List module
 * @deprecated will be removed in version 4
 */
class DatabaseRecordList extends \TYPO3\CMS\Recordlist\RecordList\DatabaseRecordList
{
    /**
     * Creates the localization panel
     *
     * @param mixed[] $row The record for which to make the localization panel.
     * @return string[]
     */
    public function makeLocalizationPanel($table, $row): array
    {
        $out = parent::makeLocalizationPanel($table, $row);

        if (!empty($out[1])) {
            if (!DeeplBackendUtility::isDeeplApiKeySet()) {
                return $out;
            }

            // glossaries should not be auto translated by DeepL
            if ($table === 'tx_wvdeepltranslate_glossaryentry') {
                return $out;
            }

            $translations = $this->translateTools->translationInfo(
                $table,
                $row['uid'],
                0,
                $row,
                $this->selFieldList
            );
            if (is_array($translations)) {
                $this->translations = $translations['translations'];
                // Traverse page translations and add icon for each language that does NOT yet exist and is included in site configuration:
                $lNew = '';
                foreach ($this->pageOverlays as $lUid_OnPage => $lsysRec) {
                    if (
                        isset($this->systemLanguagesOnPage[$lUid_OnPage])
                        && $this->isEditable($table)
                        && !isset($translations['translations'][$lUid_OnPage])
                        && $this->getBackendUserAuthentication()->checkLanguageAccess($lUid_OnPage)
                        && DeeplBackendUtility::checkCanBeTranslated(
                            ($table === 'pages') ? $row['uid'] : $row['pid'],
                            $lUid_OnPage
                        )
                    ) {
                        $language = BackendUtility::getRecord('sys_language', $lUid_OnPage, 'title');
                        $lNew .= DeeplBackendUtility::buildTranslateButton(
                            $table,
                            $row['uid'],
                            $lUid_OnPage,
                            $this->listURL(),
                            $language['title'],
                            $this->languageIconTitles[$lUid_OnPage]['flagIcon']
                        );
                    }
                }
                if ($lNew) {
                    $out[1] .= $lNew;
                }
            }
        }
        return $out;
    }
}

<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\Xclass;

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
use TYPO3\CMS\Backend\View\PageLayoutView;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * XCLASS core PageLayoutView
 *
 * @deprecated this is not longer required if feature toggle fluidBasedPageModule
 *  is enabled. Can be removed in TYPO3 11.
 */
class PageLayoutViewConfigureLanguageButton extends PageLayoutView
{
    /**
     * Creates button which is used to create copies of records..
     *
     * @param array $defaultLanguageUids Numeric array with uids of tt_content elements in the default language
     * @param int $lP Sys language UID
     * @return string "Copy languages" button, if available.
     */
    public function newLanguageButton($defaultLanguageUids, $lP)
    {
        $lP = (int)$lP;
        if (!$this->doEdit || !$lP || !$this->hasContentModificationAndAccessPermissions()) {
            return '';
        }
        $theNewButton = '';

        $localizationTsConfig = BackendUtility::getPagesTSconfig($this->id)['mod.']['web_layout.']['localization.'] ?? [];
        $allowCopy = (bool)($localizationTsConfig['enableCopy'] ?? true);
        $allowTranslate = (bool)($localizationTsConfig['enableTranslate'] ?? true);

        $allowDeeplTranslate = (bool)($localizationTsConfig['enableDeeplTranslate'] ?? true);
        $allowDeeplTranslateAuto = (bool)($localizationTsConfig['enableDeeplTranslateAuto'] ?? true);
        $allowGoogleTranslate = (bool)($localizationTsConfig['enableGoogleTranslate'] ?? true);
        $allowGoogleTranslateAuto = (bool)($localizationTsConfig['enableGoogleTranslateAuto'] ?? true);

        if (!empty($this->languageHasTranslationsCache[$lP])) {
            if (isset($this->languageHasTranslationsCache[$lP]['hasStandAloneContent'])) {
                $allowTranslate = false;
            }
            if (isset($this->languageHasTranslationsCache[$lP]['hasTranslations'])) {
                $allowCopy = $allowCopy && !$this->languageHasTranslationsCache[$lP]['hasTranslations'];
            }
        }

        if (isset($this->contentElementCache[$lP]) && is_array($this->contentElementCache[$lP])) {
            foreach ($this->contentElementCache[$lP] as $column => $records) {
                foreach ($records as $record) {
                    $key = array_search($record['l10n_source'], $defaultLanguageUids);
                    if ($key !== false) {
                        unset($defaultLanguageUids[$key]);
                    }
                }
            }
        }

        if (!empty($defaultLanguageUids)) {
            $theNewButton =
                '<a'
                . ' href="#"'
                . ' class="btn btn-default btn-sm t3js-localize disabled"'
                . ' title="' . htmlspecialchars($this->getLanguageService()->getLL('newPageContent_translate')) . '"'
                . ' data-page="' . htmlspecialchars($this->getLocalizedPageTitle()) . '"'
                . ' data-has-elements="' . (int)!empty($this->contentElementCache[$lP]) . '"'
                . ' data-allow-copy="' . (int)$allowCopy . '"'
                . ' data-allow-translate="' . (int)$allowTranslate . '"'

                . ' data-allow-deepl-translate="' . (int)$allowDeeplTranslate . '"'
                . ' data-allow-deepl-translate-auto="' . (int)$allowDeeplTranslateAuto . '"'
                . ' data-allow-google-translate="' . (int)$allowGoogleTranslate . '"'
                . ' data-allow-google-translate-auto="' . (int)$allowGoogleTranslateAuto . '"'

                . ' data-table="tt_content"'
                . ' data-page-id="' . (int)GeneralUtility::_GP('id') . '"'
                . ' data-language-id="' . $lP . '"'
                . ' data-language-name="' . htmlspecialchars($this->tt_contentConfig['languageCols'][$lP]) . '"'
                . '>'
                . $this->iconFactory->getIcon('actions-localize', Icon::SIZE_SMALL)->render()
                . ' ' . htmlspecialchars($this->getLanguageService()->getLL('newPageContent_translate'))
                . '</a>';
        }

        return $theNewButton;
    }

}

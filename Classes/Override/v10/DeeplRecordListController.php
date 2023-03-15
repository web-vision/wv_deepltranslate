<?php

declare(strict_types=1);

namespace WebVision\WvDeepltranslate\Override\v10;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Recordlist\Controller\RecordListController;
use WebVision\WvDeepltranslate\Utility\DeeplBackendUtility;

/**
 * @deprecated will be removed in version 4
 *
 * use in TYPO3 Version 9 and 10 when Feature Toggle disabled
 */
class DeeplRecordListController extends RecordListController
{
    /**
     * @param int $id
     */
    protected function languageSelector($id): string
    {
        $originalOutput = parent::languageSelector($id);
        if ($originalOutput == '') {
            return $originalOutput;
        }
        if (!DeeplBackendUtility::isDeeplApiKeySet()) {
            return $originalOutput;
        }
        $options = DeeplBackendUtility::buildTranslateDropdown(
            $this->siteLanguages,
            $this->id,
            GeneralUtility::getIndpEnv('REQUEST_URI')
        );

        if ($options == '') {
            return $originalOutput;
        }

        return str_ireplace('</div></div>', '</div>', $originalOutput)
            . '<div class="form-group">'
                . '<select class="form-control input-sm" name="createNewLanguage" onchange="window.location.href=this.options[this.selectedIndex].value">'
                    . $options
                . '</select>'
            . '</div>'
            . '</div>';
    }
}
